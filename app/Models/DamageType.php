<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * Catalog of damage / spare-part types with a standard (average) cost.
 * Used as the price list when registering damages on assets.
 */
class DamageType extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'damage_types';

    protected $rules = [
        'name' => 'required|max:100|unique:damage_types,name,NULL,id,deleted_at,NULL',
        'default_cost' => 'numeric|nullable|gte:0|max:99999999999.99',
        'category_id' => 'integer|nullable|exists:categories,id',
    ];

    protected $injectUniqueIdentifier = true;

    protected $fillable = ['name', 'default_cost', 'category_id', 'notes', 'is_critical'];

    protected $casts = [
        'is_critical' => 'boolean',
    ];

    public function setDefaultCostAttribute($value)
    {
        $value = Helper::ParseCurrency($value);
        $this->attributes['default_cost'] = ($value == 0) ? null : $value;
    }

    public function damages()
    {
        return $this->hasMany(AssetDamage::class, 'damage_type_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function isDeletable(): bool
    {
        return Gate::allows('delete', $this)
            && ($this->deleted_at == '')
            && ($this->damages()->count() === 0);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }
}
