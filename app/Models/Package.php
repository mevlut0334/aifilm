<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'title',
        'description',
        'token_amount',
        'paddle_price_id',
        'product_id',
        'is_active',
        'order',
        'is_subscription',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'token_amount' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
        'is_subscription' => 'boolean',
    ];

    public function getTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (! is_array($this->title)) {
            return (string) $this->title;
        }

        return $this->title[$locale] ?? $this->title['en'] ?? '';
    }

    public function getDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (! is_array($this->description)) {
            return (string) $this->description;
        }

        return $this->description[$locale] ?? $this->description['en'] ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeSubscription($query)
    {
        return $query->where('is_subscription', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('is_subscription', false);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
