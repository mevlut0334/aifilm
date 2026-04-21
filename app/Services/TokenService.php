<?php

namespace App\Services;

use App\Repositories\TokenRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TokenService
{
    public function __construct(
        private TokenRepository $tokenRepository
    ) {}

    public function addTokens(int $userId, int $amount, string $type, ?string $note = null, ?string $referenceId = null, ?string $referenceType = null): bool
    {
        if ($amount <= 0) {
            throw new Exception('Token amount must be positive');
        }

        try {
            DB::beginTransaction();

            // Ensure balance record exists
            if (! $this->tokenRepository->hasBalance($userId)) {
                $this->tokenRepository->createOrUpdateBalance($userId, 0);
            }

            // Increment balance
            $this->tokenRepository->incrementBalance($userId, $amount);

            // Create transaction record
            $this->tokenRepository->createTransaction([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => $type,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'note' => $note,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deductTokens(int $userId, int $amount, string $type, ?string $note = null, ?string $referenceId = null, ?string $referenceType = null): bool
    {
        if ($amount <= 0) {
            throw new Exception('Token amount must be positive');
        }

        $currentBalance = $this->getBalance($userId);
        if ($currentBalance < $amount) {
            throw new Exception('Insufficient token balance');
        }

        try {
            DB::beginTransaction();

            // Decrement balance
            $this->tokenRepository->decrementBalance($userId, $amount);

            // Create transaction record (negative amount)
            $this->tokenRepository->createTransaction([
                'user_id' => $userId,
                'amount' => -$amount,
                'type' => $type,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'note' => $note,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBalance(int $userId): int
    {
        return $this->tokenRepository->getBalance($userId);
    }

    public function getTransactions(int $userId, int $perPage = 20)
    {
        return $this->tokenRepository->getUserTransactions($userId, $perPage);
    }
}
