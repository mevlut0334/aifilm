<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomVideoSegment extends Model
{
    protected $fillable = [
        'custom_video_request_id',
        'segment_number',
        'video_url',
        'status',
        'progress',
        'failure_reason',
    ];

    protected $casts = [
        'segment_number' => 'integer',
        'progress' => 'integer',
    ];

    public function customVideoRequest(): BelongsTo
    {
        return $this->belongsTo(CustomVideoRequest::class);
    }

    public function editRequests(): HasMany
    {
        return $this->hasMany(CustomVideoEditRequest::class)->orderBy('created_at', 'desc');
    }

    public function latestEditRequest(): HasMany
    {
        return $this->hasMany(CustomVideoEditRequest::class)->latest();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
