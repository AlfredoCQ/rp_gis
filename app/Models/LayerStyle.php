<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayerStyle extends Model
{
    protected $fillable = [
        'layer_id', 'field_name', 'operator', 'value',
        'fill_color', 'stroke_color', 'opacity',
        'icon', 'label', 'sort_order',
    ];

    protected $casts = [
        'opacity'    => 'float',
        'sort_order' => 'integer',
    ];

    public function layer(): BelongsTo
    {
        return $this->belongsTo(Layer::class);
    }
}
