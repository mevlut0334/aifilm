<?php

namespace App\Repositories;

use App\Models\Purchase;

class PurchaseRepository
{
    public function findByTransactionId(string $transactionId): ?Purchase
    {
        return Purchase::where('gateway_transaction_id', $transactionId)->first();
    }

    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    public function update(Purchase $purchase, array $data): bool
    {
        return $purchase->update($data);
    }

    public function getUserPurchases(int $userId, int $perPage = 20)
    {
        return Purchase::where('user_id', $userId)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function existsByTransactionId(string $transactionId): bool
    {
        return Purchase::where('gateway_transaction_id', $transactionId)->exists();
    }

    public function findByOriginalTransactionId(string $originalTransactionId): ?Purchase
    {
        return Purchase::where('original_transaction_id', $originalTransactionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function findActiveSubscription(int $userId, string $platform): ?Purchase
    {
        return Purchase::where('user_id', $userId)
            ->where('platform', $platform)
            ->where('is_subscription', true)
            ->where('subscription_status', 'active')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function findActiveSubscriptionByOriginalId(string $originalTransactionId): ?Purchase
    {
        return Purchase::where('original_transaction_id', $originalTransactionId)
            ->where('is_subscription', true)
            ->where('subscription_status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Purchase>
     */
    public function getExpiredSubscriptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Purchase::where('is_subscription', true)
            ->where('subscription_status', 'active')
            ->where('expires_at', '<', now())
            ->get();
    }
}
