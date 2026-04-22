<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Template extends Model
{
    use HasTranslations;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'token_cost',
        'is_active',
        'landscape_video_path',
        'portrait_video_path',
        'square_video_path',
        'order',
    ];

    public array $translatable = ['title', 'description'];

    protected $casts = [
        'token_cost' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    public function getVideoPathForOrientation(string $orientation): ?string
    {
        return match ($orientation) {
            'landscape' => $this->landscape_video_path,
            'portrait' => $this->portrait_video_path,
            'square' => $this->square_video_path,
            default => null,
        };
    }

    public function hasVideoForOrientation(string $orientation): bool
    {
        return ! empty($this->getVideoPathForOrientation($orientation));
    }

    public function getVideoUrlForOrientation(string $orientation): ?string
    {
        $path = $this->getVideoPathForOrientation($orientation);

        if (! $path) {
            return null;
        }

        return url('storage/'.$path);
    }

    public function getVideoMimeType(string $orientation): string
    {
        $path = $this->getVideoPathForOrientation($orientation);

        if (! $path) {
            return 'video/mp4';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => 'video/mp4',
        };
    }
}
