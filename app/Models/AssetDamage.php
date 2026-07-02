<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * A damage recorded against a specific asset (also serves as the damage history).
 * Each record references a DamageType (which carries the standard cost) and stores the
 * real quoted cost, quantity and repair status.
 */
class AssetDamage extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'asset_damages';

    public const STATUS_REPORTED = 'reported';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_PURCHASE_REQUEST = 'purchase_request';
    public const STATUS_REPAIRED = 'repaired';
    public const STATUS_DISCARDED = 'discarded';

    /** All selectable statuses, in workflow order. */
    public const STATUSES = [
        self::STATUS_REPORTED,
        self::STATUS_QUOTED,
        self::STATUS_PURCHASE_REQUEST,
        self::STATUS_REPAIRED,
        self::STATUS_DISCARDED,
    ];

    /** Statuses that still represent an outstanding (unrepaired) damage. */
    public const PENDING_STATUSES = [self::STATUS_REPORTED, self::STATUS_QUOTED, self::STATUS_PURCHASE_REQUEST];

    protected $rules = [
        'asset_id' => 'required|integer|exists:assets,id',
        'damage_type_id' => 'required|integer|exists:damage_types,id',
        'quantity' => 'required|integer|gte:1',
        'cost' => 'numeric|nullable|gte:0|max:99999999999.99',
        'status' => 'required|in:reported,quoted,purchase_request,repaired,discarded',
        'supplier_id' => 'integer|nullable|exists:suppliers,id',
        'reported_at' => 'date|nullable',
        'repaired_at' => 'date|nullable',
    ];

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'asset_id',
        'damage_type_id',
        'quantity',
        'cost',
        'status',
        'supplier_id',
        'erp_purchase_code',
        'purchase_request_id',
        'reported_by',
        'reported_at',
        'repaired_at',
        'notes',
    ];

    protected $casts = [
        'reported_at' => 'date',
        'repaired_at' => 'date',
        'quantity' => 'integer',
    ];

    public function setCostAttribute($value)
    {
        $value = Helper::ParseCurrency($value);
        $this->attributes['cost'] = ($value == 0) ? null : $value;
    }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = ($value == '') ? null : $value;
    }

    public function isPending(): bool
    {
        return in_array($this->status, self::PENDING_STATUSES, true);
    }

    /**
     * Build the "damages by model" pivot: one row per model, one column per damage
     * type (with quantities), plus totals. Shared by the report, the mailable and
     * the scheduled command.
     *
     * @return array{types: array, rows: array, colTotals: array, grandQty: int, grandCost: float}
     */
    public static function byModelMatrix(bool $onlyPending = false): array
    {
        $query = \Illuminate\Support\Facades\DB::table('asset_damages')
            ->join('assets', 'assets.id', '=', 'asset_damages.asset_id')
            ->join('models', 'models.id', '=', 'assets.model_id')
            ->leftJoin('categories', 'categories.id', '=', 'models.category_id')
            ->join('damage_types', 'damage_types.id', '=', 'asset_damages.damage_type_id')
            ->whereNull('asset_damages.deleted_at')
            ->whereNull('assets.deleted_at')
            ->groupBy('models.id', 'models.name', 'categories.name', 'damage_types.id', 'damage_types.name')
            ->select(
                'models.id as model_id',
                'models.name as model',
                'categories.name as category',
                'damage_types.name as damage_type',
                \Illuminate\Support\Facades\DB::raw('SUM(asset_damages.quantity) as qty'),
                \Illuminate\Support\Facades\DB::raw('SUM(asset_damages.cost) as cost')
            );

        if ($onlyPending) {
            $query->whereIn('asset_damages.status', self::PENDING_STATUSES);
        }

        $data = $query->get();

        $types = $data->pluck('damage_type')->unique()->sort()->values()->all();

        $rows = [];
        $colTotals = array_fill_keys($types, 0);
        $grandQty = 0;
        $grandCost = 0;

        foreach ($data as $r) {
            if (! isset($rows[$r->model_id])) {
                $rows[$r->model_id] = ['model' => $r->model, 'category' => $r->category, 'cells' => array_fill_keys($types, 0), 'total_qty' => 0, 'total_cost' => 0];
            }
            $rows[$r->model_id]['cells'][$r->damage_type] += (int) $r->qty;
            $rows[$r->model_id]['total_qty'] += (int) $r->qty;
            $rows[$r->model_id]['total_cost'] += (float) $r->cost;
            $colTotals[$r->damage_type] += (int) $r->qty;
            $grandQty += (int) $r->qty;
            $grandCost += (float) $r->cost;
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['model'], $b['model']));

        return compact('types', 'rows', 'colTotals', 'grandQty', 'grandCost');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')->withTrashed();
    }

    public function damageType()
    {
        return $this->belongsTo(DamageType::class, 'damage_type_id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by')->withTrashed();
    }

    public function images()
    {
        return $this->hasMany(DamageImage::class, 'asset_damage_id');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
