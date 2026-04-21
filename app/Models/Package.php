<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'title',
        'description',
        'token_amount',
        'paddle_price_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'token_amount' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function getTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->title[$locale] ?? $this->title['en'] ?? null;
    }

    public function getDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->description[$locale] ?? $this->description['en'] ?? null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
