<?php

namespace App\Console\Commands;

use App\Mail\DamageReportMail;
use App\Models\ReportEmailSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';

    protected $description = 'Send any report email schedules that are due.';

    public function handle(): int
    {
        $due = ReportEmailSchedule::where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No report schedules due.');

            return self::SUCCESS;
        }

        foreach ($due as $schedule) {
            try {
                $recipients = $schedule->recipientList();

                if (empty($recipients)) {
                    $this->warn("Schedule #{$schedule->id} has no valid recipients, skipping.");

                    continue;
                }

                $mailable = ReportEmailSchedule::mailableFor($schedule->report_key, (bool) $schedule->only_pending);

                if (! $mailable) {
                    $this->warn("Schedule #{$schedule->id} has unknown report_key '{$schedule->report_key}', skipping.");

                    continue;
                }

                Mail::to($recipients)->send($mailable);

                // Advance to the next future slot, preserving weekday/time and
                // skipping any missed occurrences (no backlog of catch-up emails).
                $next = $schedule->next_run_at->copy();
                do {
                    $next = ReportEmailSchedule::advance($next, $schedule->frequency);
                } while ($next->lte(now()));

                $schedule->update([
                    'last_sent_at' => now(),
                    'next_run_at' => $next,
                ]);

                $this->info("Sent schedule #{$schedule->id} to ".implode(', ', $recipients));
            } catch (\Throwable $e) {
                Log::error('Scheduled report send failed for schedule #'.$schedule->id.': '.$e->getMessage());
                $this->error("Schedule #{$schedule->id}: ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
