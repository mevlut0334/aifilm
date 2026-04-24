<?php

namespace App\Providers;

use App\Listeners\HandlePaddleWebhook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated;
use Laravel\Paddle\Events\TransactionCompleted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom Blade directive for safe translation
        Blade::directive('trans_safe', function ($expression) {
            return "<?php 
                \$translation = __($expression);
                echo is_array(\$translation) 
                    ? (\$translation[app()->getLocale()] ?? \$translation['en'] ?? $expression)
                    : \$translation;
            ?>";
        });

        // Register Paddle webhook event listeners
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
