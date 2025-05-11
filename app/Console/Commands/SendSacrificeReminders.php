<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Notifications\SacrificeReminderSmsNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSacrificeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'varaid:send-sacrifice-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels SMS pour les abattements prévus le lendemain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Les dates spécifiques pour l'Aïd 2025
        $firstDay = Carbon::parse('2025-06-01'); // 1er jour de l'Aïd
        $secondDay = Carbon::parse('2025-06-02'); // 2ème jour de l'Aïd
        
        // Vérifier si nous sommes à la veille d'un des jours de l'Aïd
        $tomorrow = Carbon::tomorrow();
        $isBeforeFirstEidDay = $tomorrow->isSameDay($firstDay);
        $isBeforeSecondEidDay = $tomorrow->isSameDay($secondDay);
        
        if (!$isBeforeFirstEidDay && !$isBeforeSecondEidDay) {
            $this->info('Today is not a day before Eid, no reminders needed');
            Log::info('No sacrifice reminders needed today - not a day before Eid');
            return 0;
        }
        
        // Déterminer quel jour de l'Aïd c'est
        $eidDay = $isBeforeFirstEidDay ? '1er jour de l\'Aïd' : '2ème jour de l\'Aïd';
        $this->info("Tomorrow is the {$eidDay}, sending sacrifice reminders");
        
        // Préparer la requête pour les réservations concernées
        $query = Reservation::with(['user', 'slot'])
            ->where('status', 'confirmed');
            
        // Filtre par jour spécifique en fonction de la logique métier
        // NB: Cette partie dépend de votre modèle de données exact, à ajuster si nécessaire
        if ($isBeforeFirstEidDay) {
            // Pour le premier jour de l'Aïd
            $query->whereDate('date', '=', $firstDay->subDay()->format('Y-m-d'));
        } else {
            // Pour le deuxième jour de l'Aïd
            $query->whereDate('date', '=', $secondDay->subDay()->format('Y-m-d'));
        }
        
        $reservations = $query->get();
        
        $this->info("Found {$reservations->count()} reservations for {$eidDay}");
        Log::info("Found {$reservations->count()} reservations scheduled for sacrifice tomorrow", ['eid_day' => $eidDay]);
        
        $notificationCount = 0;
        
        foreach ($reservations as $reservation) {
            if (!$reservation->user) {
                $this->warn("Reservation {$reservation->id} has no user associated");
                Log::warning("Skipping sacrifice reminder for reservation without user", ['reservation_id' => $reservation->id]);
                continue;
            }
            
            try {
                $reservation->user->notify(new SacrificeReminderSmsNotification($reservation));
                $this->info("Sent sacrifice reminder for reservation {$reservation->code} to {$reservation->user->phone}");
                Log::info("Sent sacrifice reminder SMS", [
                    'reservation_code' => $reservation->code,
                    'user_id' => $reservation->user->id,
                    'phone' => $reservation->user->phone
                ]);
                $notificationCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send sacrifice reminder for reservation {$reservation->code}: {$e->getMessage()}");
                Log::error("Failed to send sacrifice reminder SMS", [
                    'reservation_code' => $reservation->code,
                    'user_id' => $reservation->user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("{$notificationCount} sacrifice reminders sent successfully");
        Log::info("Sacrifice reminder SMS sending completed", ['sent_count' => $notificationCount]);
        
        return 0;
    }
}