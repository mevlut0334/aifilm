<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'platform',
        'amount_paid',
        'currency',
        'gateway_transaction_id',
        'token_amount',
        'status',
        'is_subscription',
        'expires_at',
        'original_transaction_id',
        'auto_renewing',
        'subscription_status',
    ];

    protected $casts = [
        'amount_paid'      => 'decimal:2',
        'token_amount'     => 'integer',
        'is_subscription'  => 'boolean',
        'auto_renewing'    => 'boolean',
        'expires_at'       => 'datetime',
    ];

    // Aktif abonelik mi?
    public function isActiveSubscription(): bool
    {
        return $this->is_subscription
            && $this->subscription_status === 'active'
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    // Süresi dolmuş mu?
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // Scope: sadece abonelikler
    public function scopeSubscriptions($query)
    {
        return $query->where('is_subscription', true);
    }

    // Scope: aktif abonelikler
    public function scopeActive($query)
    {
        return $query->where('subscription_status', 'active')
            ->where('expires_at', '>', now());
    }

    // Scope: platforma göre
    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
