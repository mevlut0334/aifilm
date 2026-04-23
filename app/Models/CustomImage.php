<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomImage extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'prompt',
        'format',
        'input_image_path',
        'status',
        'progress',
        'admin_image_url',
        'token_cost',
        'failure_reason',
    ];

    protected $casts = [
        'token_cost' => 'integer',
        'progress' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {
            if (empty($image->uuid)) {
                $image->uuid = (string) Str::uuid();
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

    public function referenceImages(): HasMany
    {
        return $this->hasMany(CustomImageReference::class)->orderBy('order');
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
}
