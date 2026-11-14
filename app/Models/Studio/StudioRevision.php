<?php

namespace App\Models\Studio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioRevision extends Model
{
    protected $table = 'studio_revisions';

    protected $fillable = [
        'studio_request_id',
        'description',
        'reference_video_url',
        'status',
    ];

    public function studioRequest(): BelongsTo
    {
        return $this->belongsTo(StudioRequest::class, 'studio_request_id');
    }
}
