<?php

namespace App\Providers;

use App\Requirements\UserProfileRequirements; 
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\BrevoSmsChannel;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // app/Providers/AppServiceProvider.php

    public function register()
    {
        $this->app->bind(UserProfileRequirements::class, function ($app) {
            return new UserProfileRequirements();
        });
    }

    /**
     * Bootstrap any application services.
     */

    public function boot()
    {
        Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.utf8');

        // Enregistrer le canal Brevo SMS
        Notification::extend('brevo_sms', function ($app) {
            return new BrevoSmsChannel();
        });
    }
}
