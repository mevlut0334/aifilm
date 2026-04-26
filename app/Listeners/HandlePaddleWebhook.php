<?php

namespace App\Listeners;

use App\Models\Package;
use App\Repositories\PurchaseRepository;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated;
use Laravel\Paddle\Events\TransactionCompleted;

class HandlePaddleWebhook
{
    public function __construct(
        private TokenService $tokenService,
        private PurchaseRepository $purchaseRepository
    ) {}

    public function handleTransactionCompleted(TransactionCompleted $event): void
    {
        try {
            $transaction = $event->transaction;
            $customData = $transaction->custom_data ?? [];

            Log::info('Paddle Transaction Completed', [
                'transaction_id' => $transaction->id,
                'custom_data' => $customData,
            ]);

            $userId = $customData['user_id'] ?? null;
            $packageId = $customData['package_id'] ?? null;

            if (! $userId || ! $packageId) {
                Log::warning('Missing user_id or package_id in custom data', [
                    'transaction_id' => $transaction->id,
                    'custom_data' => $customData,
                ]);
                return;
            }

            if ($this->purchaseRepository->existsByTransactionId($transaction->id)) {
                Log::info('Transaction already processed', ['transaction_id' => $transaction->id]);
                return;
            }

            $package = Package::find($packageId);
            if (! $package) {
                Log::error('Package not found', ['package_id' => $packageId]);
                return;
            }

            DB::beginTransaction();
            try {
                $purchase = $this->purchaseRepository->create([
                    'user_id' => $userId,
                    'package_id' => $package->id,
                    'platform' => 'web',
                    'amount_paid' => $transaction->details->totals->total / 100,
                    'currency' => $transaction->currency_code,
                    'gateway_transaction_id' => $transaction->id,
                    'token_amount' => $package->token_amount,
                    'status' => 'completed',
                ]);

                $this->tokenService->resetAndAddTokens(
                    $userId,
                    $package->token_amount,
                    'purchase',
                    "Package purchase: {$package->getTitle()}",
                    $transaction->id,
                    'paddle'
                );

                DB::commit();

                Log::info('Tokens reset and added successfully', [
                    'user_id' => $userId,
                    'tokens' => $package->token_amount,
                    'purchase_id' => $purchase->id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to process transaction: '.$e->getMessage(), [
                    'transaction_id' => $transaction->id,
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Webhook handling error: '.$e->getMessage());
        }
    }

    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        Log::info('Paddle Subscription Created', [
            'subscription_id' => $event->subscription->id,
            'customer_id' => $event->subscription->customer_id,
        ]);
    }

    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        Log::info('Paddle Subscription Updated', [
            'subscription_id' => $event->subscription->id,
            'status' => $event->subscription->status,
        ]);
    }

    public function handleRawTransaction(array $transaction): void
    {
        $customData = $transaction['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;
        $packageId = $customData['package_id'] ?? null;
        $transactionId = $transaction['id'] ?? null;

        Log::info('Raw transaction handler called', [
            'transaction_id' => $transactionId,
            'user_id' => $userId,
            'package_id' => $packageId,
        ]);

        if (! $userId || ! $packageId || ! $transactionId) {
            Log::warning('Missing required fields in raw transaction', compact('userId', 'packageId', 'transactionId'));
            return;
        }

        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            Log::info('Transaction already processed', ['transaction_id' => $transactionId]);
            return;
        }

        $package = Package::find($packageId);
        if (! $package) {
            Log::error('Package not found', ['package_id' => $packageId]);
            return;
        }

        DB::beginTransaction();
        try {
            $purchase = $this->purchaseRepository->create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'platform' => 'web',
                'amount_paid' => ($transaction['details']['totals']['total'] ?? 0) / 100,
                'currency' => $transaction['currency_code'] ?? 'USD',
                'gateway_transaction_id' => $transactionId,
                'token_amount' => $package->token_amount,
                'status' => 'completed',
            ]);

            $this->tokenService->resetAndAddTokens(
                $userId,
                $package->token_amount,
                'purchase',
                "Package purchase: {$package->getTitle()}",
                $transactionId,
                'paddle'
            );

            DB::commit();

            Log::info('Tokens reset and added via raw webhook', [
                'user_id' => $userId,
                'tokens' => $package->token_amount,
                'purchase_id' => $purchase->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Raw webhook processing failed: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
            ]);
        }
    }
}
