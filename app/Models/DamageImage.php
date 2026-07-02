<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

/**
 * A photo attached to an AssetDamage record. Stored on the public disk
 * under uploads/damages.
 */
class DamageImage extends SnipeModel
{
    protected $table = 'asset_damage_images';

    // Path is relative to the 'public' disk root (public/uploads), so files
    // land in public/uploads/damages and are served at APP_URL/uploads/damages.
    public const UPLOAD_PATH = 'damages';

    protected $fillable = [
        'asset_damage_id',
        'filename',
        'created_by',
    ];

    public function damage()
    {
        return $this->belongsTo(AssetDamage::class, 'asset_damage_id');
    }

    /**
     * Public URL for the stored photo.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url(self::UPLOAD_PATH.'/'.$this->filename);
    }
}
