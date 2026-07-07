<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'color', 'icon', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(MapFeature::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
