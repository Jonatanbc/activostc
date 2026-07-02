<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * A recurring schedule to email a report to one or more recipients.
 */
class ReportEmailSchedule extends Model
{
    protected $table = 'report_email_schedules';

    public const FREQ_WEEKLY = 'weekly';
    public const FREQ_BIWEEKLY = 'biweekly';   // every 2 weeks
    public const FREQ_MONTHLY = 'monthly';

    /** Reports are scheduled in Colombia time. */
    public const TIMEZONE = 'America/Bogota';

    protected $fillable = [
        'report_key',
        'recipients',
        'frequency',
        'send_day',
        'send_time',
        'only_pending',
        'is_active',
        'next_run_at',
        'last_sent_at',
        'created_by',
    ];

    protected $casts = [
        'send_day' => 'integer',
        'only_pending' => 'boolean',
        'is_active' => 'boolean',
        'next_run_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Recipients as an array of email addresses.
     */
    public function recipientList(): array
    {
        return collect(preg_split('/[,;\s]+/', (string) $this->recipients, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * First run: next occurrence of the chosen weekday at the chosen time (Colombia),
     * returned in the app timezone for storage/comparison.
     *
     * @param  int  $dayOfWeek  0=Sunday .. 6=Saturday
     * @param  string  $time  "HH:MM"
     */
    public static function computeFirstRun(int $dayOfWeek, string $time): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        $nowCo = now()->copy()->setTimezone(self::TIMEZONE);
        $candidate = $nowCo->copy()->setTime($h, $m, 0);

        // Advance day by day until it lands on the desired weekday and is in the future.
        while ($candidate->dayOfWeek !== $dayOfWeek || $candidate->lte($nowCo)) {
            $candidate->addDay()->setTime($h, $m, 0);
        }

        return $candidate->setTimezone(config('app.timezone'));
    }

    /**
     * Advance a run time by one cadence (weekday and time-of-day preserved).
     */
    public static function advance(Carbon $from, string $frequency): Carbon
    {
        return match ($frequency) {
            self::FREQ_WEEKLY => $from->copy()->addWeek(),
            self::FREQ_BIWEEKLY => $from->copy()->addWeeks(2),
            self::FREQ_MONTHLY => $from->copy()->addMonthNoOverflow(),
            default => $from->copy()->addWeek(),
        };
    }

    public function frequencyLabel(): string
    {
        return trans('admin/damages/general.freq_'.$this->frequency);
    }

    /**
     * Localized weekday name for the chosen send day.
     */
    public function dayLabel(): string
    {
        if ($this->send_day === null) {
            return '—';
        }

        return Carbon::now()->startOfWeek(CarbonInterface::SUNDAY)
            ->addDays($this->send_day)->translatedFormat('l');
    }

    /**
     * Next run shown in Colombia time.
     */
    public function nextRunColombia(): ?Carbon
    {
        return $this->next_run_at?->copy()->setTimezone(self::TIMEZONE);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Resolve the Mailable for a given report key.
     */
    public static function mailableFor(string $reportKey, bool $onlyPending = false): ?\Illuminate\Mail\Mailable
    {
        return match ($reportKey) {
            'damages_by_model' => new \App\Mail\DamageReportMail($onlyPending),
            'damages_list' => new \App\Mail\DamagesListMail($onlyPending),
            default => null,
        };
    }
}
