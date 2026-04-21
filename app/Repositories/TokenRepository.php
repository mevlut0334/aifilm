<?php

namespace App\Repositories;

use App\Models\TokenBalance;
use App\Models\TokenTransaction;

class TokenRepository
{
    public function getBalance(int $userId): int
    {
        $balance = TokenBalance::where('user_id', $userId)->first();

        return $balance ? $balance->balance : 0;
    }

    public function createOrUpdateBalance(int $userId, int $amount): TokenBalance
    {
        return TokenBalance::updateOrCreate(
            ['user_id' => $userId],
            ['balance' => $amount]
        );
    }

    public function incrementBalance(int $userId, int $amount): void
    {
        TokenBalance::where('user_id', $userId)->increment('balance', $amount);
    }

    public function decrementBalance(int $userId, int $amount): void
    {
        TokenBalance::where('user_id', $userId)->decrement('balance', $amount);
    }

    public function createTransaction(array $data): TokenTransaction
    {
        return TokenTransaction::create($data);
    }

    public function getUserTransactions(int $userId, int $perPage = 20)
    {
        return TokenTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function hasBalance(int $userId): bool
    {
        return TokenBalance::where('user_id', $userId)->exists();
    }

    public function getBalanceModel(int $userId): ?TokenBalance
    {
        return TokenBalance::where('user_id', $userId)->first();
    }
}
