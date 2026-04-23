<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomVideoRequest extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'prompt',
        'format',
        'input_image_path',
        'status',
        'token_cost',
        'token_deducted',
        'failure_reason',
    ];

    protected $casts = [
        'token_cost' => 'integer',
        'token_deducted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(CustomVideoSegment::class)->orderBy('segment_number');
    }

    public function referenceImages(): HasMany
    {
        return $this->hasMany(CustomVideoReferenceImage::class)->orderBy('order');
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

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getOverallProgress(): int
    {
        $segments = $this->segments;

        if ($segments->isEmpty()) {
            return 0;
        }

        $totalProgress = $segments->sum('progress');

        return (int) ($totalProgress / $segments->count());
    }
}
