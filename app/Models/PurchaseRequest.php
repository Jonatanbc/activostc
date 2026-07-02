<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A purchase request groups several damages together so a quotation can be
 * requested from a supplier.
 */
class PurchaseRequest extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'purchase_requests';

    public const STATUS_OPEN = 'open';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_QUOTED,
        self::STATUS_ORDERED,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'status',
        'supplier_id',
        'notes',
        'created_by',
    ];

    public function damages()
    {
        return $this->hasMany(AssetDamage::class, 'purchase_request_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Human-friendly reference, e.g. SC-00042.
     */
    public function getReferenceAttribute(): string
    {
        return 'SC-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Total estimated cost of the grouped damages.
     */
    public function getTotalCostAttribute()
    {
        return $this->damages->sum('cost');
    }

    /**
     * Total quantity of components across the grouped damages.
     */
    public function getComponentsCountAttribute(): int
    {
        return (int) $this->damages->sum('quantity');
    }
}
