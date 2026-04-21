<?php

use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'localize' => LaravelLocalizationRoutes::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeCookieRedirect' => LocaleCookieRedirect::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
            'setLocaleFromHeader' => SetLocaleFromHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
