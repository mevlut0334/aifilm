<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilePackage extends Model
{
    protected $fillable = [
        'title',
        'description',
        'token_amount',
        'ios_product_id',
        'android_product_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'token_amount' => 'integer',
        'order'       => 'integer',
        'is_active'   => 'boolean',
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

    public function scopeForIos($query)
    {
        return $query->whereNotNull('ios_product_id');
    }

    public function scopeForAndroid($query)
    {
        return $query->whereNotNull('android_product_id');
    }
}
