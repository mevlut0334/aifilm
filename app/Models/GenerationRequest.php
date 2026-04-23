<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GenerationRequest extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'template_id',
        'type',
        'orientation',
        'description',
        'token_cost',
        'status',
        'progress',
        'output_url',
        'input_image_path',
        'failure_reason',
    ];

    protected $casts = [
        'token_cost' => 'integer',
        'progress' => 'integer',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id', 'uuid');
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

    public function isTemplateBasedRequest(): bool
    {
        return in_array($this->type, ['template_image', 'template_video']);
    }

    public function isCustomRequest(): bool
    {
        return in_array($this->type, ['custom_image', 'custom_video']);
    }
}
