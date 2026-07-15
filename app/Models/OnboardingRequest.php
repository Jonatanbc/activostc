<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * "Gestión de ingreso del personal": an immediate boss requests the resources and
 * platforms for a new hire. IT reviews and fulfills (creates the account and does
 * the real asset/accessory checkouts and platform provisioning).
 */
class OnboardingRequest extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'onboarding_requests';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_FULFILLED,
    ];

    public const ENTRY_NEW = 'nuevo';
    public const ENTRY_REPLACEMENT = 'reemplazo';

    /** Platforms a new hire may need. Names are proper nouns (not translated). */
    public const PLATFORMS = ['OTM', 'ERP', 'PIT', 'Optopus', 'RNDC'];

    protected $rules = [
        'position' => 'required|string|max:191',
        'employee_name' => 'required|string|max:191',
        'entry_type' => 'required|in:nuevo,reemplazo',
        'replaces_user_id' => 'nullable|integer|exists:users,id',
        'asset_id' => 'nullable|integer|exists:assets,id',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,approved,rejected,fulfilled',
    ];

    protected $fillable = [
        'requested_by', 'position', 'employee_name', 'entry_type', 'replaces_user_id',
        'asset_id', 'accessories', 'platforms', 'notes', 'status', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'accessories' => 'array',
        'platforms' => 'array',
        'processed_at' => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    public function replacesUser()
    {
        return $this->belongsTo(User::class, 'replaces_user_id')->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')->withTrashed();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReplacement(): bool
    {
        return $this->entry_type === self::ENTRY_REPLACEMENT;
    }
}
