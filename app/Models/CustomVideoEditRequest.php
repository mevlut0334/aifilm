<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomVideoEditRequest extends Model
{
    protected $fillable = [
        'custom_video_segment_id',
        'edit_prompt',
        'edit_cost',
        'token_deducted',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'edit_cost' => 'integer',
        'token_deducted' => 'boolean',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(CustomVideoSegment::class, 'custom_video_segment_id');
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

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
