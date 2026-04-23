<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomImageReference extends Model
{
    protected $fillable = [
        'custom_image_id',
        'image_path',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function customImage(): BelongsTo
    {
        return $this->belongsTo(CustomImage::class);
    }
}
