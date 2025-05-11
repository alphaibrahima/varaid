<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Services\BrevoSmsService;
use App\Channels\BrevoSmsChannel;

class BrevoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BrevoSmsService::class, function ($app) {
            return new BrevoSmsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrer le canal de notification
        Notification::extend('brevo_sms', function ($app) {
            return $app->make(BrevoSmsChannel::class);
        });
    }
}