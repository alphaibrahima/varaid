<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Notifications\SelectionReminderSmsNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSelectionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'varaid:send-selection-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels SMS pour les rendez-vous de sélection d\'agneau du lendemain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Calculer la date de demain
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        
        $this->info("Checking for reservations scheduled for selection on {$tomorrow}");
        
        // Récupérer toutes les réservations pour demain où skip_selection est false
        $reservations = Reservation::with(['user', 'slot'])
            ->where('date', $tomorrow)
            ->where('skip_selection', false)
            ->where('status', 'confirmed')
            ->get();
            
        $this->info("Found {$reservations->count()} reservations for tomorrow");
        Log::info("Found {$reservations->count()} reservations scheduled for selection tomorrow", ['date' => $tomorrow]);
        
        $notificationCount = 0;
        
        foreach ($reservations as $reservation) {
            if (!$reservation->user) {
                $this->warn("Reservation {$reservation->id} has no user associated");
                Log::warning("Skipping selection reminder for reservation without user", ['reservation_id' => $reservation->id]);
                continue;
            }
            
            try {
                $reservation->user->notify(new SelectionReminderSmsNotification($reservation));
                $this->info("Sent selection reminder for reservation {$reservation->code} to {$reservation->user->phone}");
                Log::info("Sent selection reminder SMS", [
                    'reservation_code' => $reservation->code,
                    'user_id' => $reservation->user->id,
                    'phone' => $reservation->user->phone
                ]);
                $notificationCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send selection reminder for reservation {$reservation->code}: {$e->getMessage()}");
                Log::error("Failed to send selection reminder SMS", [
                    'reservation_code' => $reservation->code,
                    'user_id' => $reservation->user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("{$notificationCount} selection reminders sent successfully");
        Log::info("Selection reminder SMS sending completed", ['sent_count' => $notificationCount]);
        
        return 0;
    }
}