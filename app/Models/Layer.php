<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Layer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'type',
        'color', 'icon', 'opacity',
        'min_zoom', 'max_zoom',
        'is_active', 'is_public',
        'sort_order', 'created_by',
    ];

    protected $casts = [
        'opacity'   => 'float',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'min_zoom'  => 'integer',
        'max_zoom'  => 'integer',
        'sort_order'=> 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function features(): HasMany
    {
        return $this->hasMany(MapFeature::class);
    }

    public function styles(): HasMany
    {
        return $this->hasMany(LayerStyle::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FeatureField::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getFeaturesCountAttribute(): int
    {
        return $this->features()->count();
    }
}
