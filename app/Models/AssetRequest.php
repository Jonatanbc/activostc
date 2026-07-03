<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * A request (with a small questionnaire) to assign an available asset to a person.
 * Created from the "Disponibilidad de equipos" module; reviewed by IT.
 */
class AssetRequest extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'asset_requests';

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

    public const ACCOUNT_NEW = 'nueva';
    public const ACCOUNT_REPLACEMENT = 'reemplazo';

    protected $rules = [
        'asset_id' => 'required|integer|exists:assets,id',
        'assignee_name' => 'required|string|max:191',
        'assignee_id_number' => 'nullable|string|max:100',
        'assignee_position' => 'nullable|string|max:191',
        'assignee_location' => 'nullable|string|max:191',
        'account_type' => 'nullable|in:nueva,reemplazo',
        'replaces_person' => 'nullable|string|max:191',
        'needed_at' => 'nullable|date',
        'justification' => 'nullable|string',
        'status' => 'required|in:pending,approved,rejected,fulfilled',
    ];

    protected $fillable = [
        'asset_id', 'requested_by', 'assignee_name', 'assignee_id_number',
        'assignee_position', 'assignee_location', 'account_type', 'replaces_person',
        'needed_at', 'justification', 'status', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'needed_at' => 'date',
        'processed_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')->withTrashed();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
