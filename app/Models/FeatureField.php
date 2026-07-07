<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureField extends Model
{
    protected $fillable = [
        'layer_id', 'name', 'label', 'type',
        'options', 'placeholder', 'default_value',
        'is_required', 'is_searchable', 'is_filterable',
        'is_visible_in_popup', 'sort_order',
    ];

    protected $casts = [
        'options'              => 'array',
        'is_required'          => 'boolean',
        'is_searchable'        => 'boolean',
        'is_filterable'        => 'boolean',
        'is_visible_in_popup'  => 'boolean',
        'sort_order'           => 'integer',
    ];

    public function layer(): BelongsTo
    {
        return $this->belongsTo(Layer::class);
    }
}
