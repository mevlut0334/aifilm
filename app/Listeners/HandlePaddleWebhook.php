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

    /**
     * Handle transaction completed (for one-time purchases and subscription first payment)
     */
    public function handleTransactionCompleted(TransactionCompleted $event): void
    {
        try {
            $transaction = $event->transaction;
            $customData = $transaction->custom_data ?? [];

            Log::info('Paddle Transaction Completed', [
                'transaction_id' => $transaction->id,
                'custom_data' => $customData,
            ]);

            // Get user_id and package_id from custom data
            $userId = $customData['user_id'] ?? null;
            $packageId = $customData['package_id'] ?? null;

            if (! $userId || ! $packageId) {
                Log::warning('Missing user_id or package_id in custom data', [
                    'transaction_id' => $transaction->id,
                    'custom_data' => $customData,
                ]);

                return;
            }

            // Check if already processed (prevent duplicates)
            if ($this->purchaseRepository->existsByTransactionId($transaction->id)) {
                Log::info('Transaction already processed', ['transaction_id' => $transaction->id]);

                return;
            }

            // Get package
            $package = Package::find($packageId);
            if (! $package) {
                Log::error('Package not found', ['package_id' => $packageId]);

                return;
            }

            DB::beginTransaction();
            try {
                // Create purchase record
                $purchase = $this->purchaseRepository->create([
                    'user_id' => $userId,
                    'package_id' => $package->id,
                    'platform' => 'web',
                    'amount_paid' => $transaction->details->totals->total / 100, // Convert cents to dollars
                    'currency' => $transaction->currency_code,
                    'gateway_transaction_id' => $transaction->id,
                    'token_amount' => $package->token_amount,
                    'status' => 'completed',
                ]);

                // Add tokens to user
                $this->tokenService->addTokens(
                    $userId,
                    $package->token_amount,
                    'purchase',
                    $package->is_subscription
                        ? "Monthly subscription: {$package->getTitle()}"
                        : "Package purchase: {$package->getTitle()}",
                    $transaction->id,
                    'paddle'
                );

                DB::commit();

                Log::info('Tokens added successfully', [
                    'user_id' => $userId,
                    'package_id' => $packageId,
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

    /**
     * Handle subscription created (first subscription payment)
     */
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        Log::info('Paddle Subscription Created', [
            'subscription_id' => $event->subscription->id,
            'customer_id' => $event->subscription->customer_id,
        ]);

        // TransactionCompleted will handle the token addition
    }

    /**
     * Handle subscription updated (renewal payments)
     */
    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        Log::info('Paddle Subscription Updated', [
            'subscription_id' => $event->subscription->id,
            'status' => $event->subscription->status,
        ]);

        // If it's a renewal, TransactionCompleted event will fire
        // and handle the token addition automatically
    }
}
