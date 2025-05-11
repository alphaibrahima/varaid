<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes de console de l'application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SendSelectionReminders::class,
        \App\Console\Commands\SendSacrificeReminders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ...autres tâches planifiées...
        
        // Envoyer les rappels de sélection tous les jours à 18h
        $schedule->command('varaid:send-selection-reminders')
                ->dailyAt('18:00')
                ->appendOutputTo(storage_path('logs/selection-reminders.log'));
        
        // Envoyer les rappels d'abattement tous les jours à 18h
        $schedule->command('varaid:send-sacrifice-reminders')
                ->dailyAt('18:00')
                ->appendOutputTo(storage_path('logs/sacrifice-reminders.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}