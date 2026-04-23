<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomVideoReferenceImage extends Model
{
    protected $fillable = [
        'custom_video_request_id',
        'image_path',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function customVideoRequest(): BelongsTo
    {
        return $this->belongsTo(CustomVideoRequest::class);
    }
}
