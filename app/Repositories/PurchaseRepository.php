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
}
