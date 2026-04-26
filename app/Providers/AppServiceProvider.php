<?php

namespace App\Providers;

use App\Listeners\HandlePaddleWebhook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated;
use Laravel\Paddle\Events\TransactionCompleted;
use Laravel\Paddle\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('trans_safe', function ($expression) {
            return "<?php
                \$translation = __($expression);
                echo is_array(\$translation)
                    ? (\$translation[app()->getLocale()] ?? \$translation['en'] ?? $expression)
                    : \$translation;
            ?>";
        });

        // Fallback: WebhookReceived (customer tablosuna bakmaz, her zaman tetiklenir)
        Event::listen(WebhookReceived::class, function ($event) {
            $payload = $event->payload;
            $eventType = $payload['event_type'] ?? null;

            Log::info('WebhookReceived event fired', ['event_type' => $eventType]);

            if ($eventType === 'transaction.completed') {
                $transaction = $payload['data'] ?? [];
                app(HandlePaddleWebhook::class)->handleRawTransaction($transaction);
            }
        });

        Event::listen(
            TransactionCompleted::class,
            [HandlePaddleWebhook::class, 'handleTransactionCompleted']
        );

        Event::listen(
            SubscriptionCreated::class,
            [HandlePaddleWebhook::class, 'handleSubscriptionCreated']
        );

        Event::listen(
            SubscriptionUpdated::class,
            [HandlePaddleWebhook::class, 'handleSubscriptionUpdated']
        );
    }
}
