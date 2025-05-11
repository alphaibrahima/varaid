<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class SacrificeReminderSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['brevo_sms'];
    }

    /**
     * Get the Brevo SMS representation of the notification.
     */
    public function toBrevoSms($notifiable)
    {
        // Récupérer le jour de l'Aïd
        $eidDay = $this->reservation->eid_day;
        
        // Formater l'heure
        $time = $this->reservation->slot->start_time;
        if (is_string($time)) {
            $time = substr($time, 0, 5);
        } elseif (is_object($time) && method_exists($time, 'format')) {
            $time = $time->format('H:i');
        }
        
        return "VARAID: RAPPEL! L'abattage de votre agneau (réservation #{$this->reservation->code}) est prévu demain, {$eidDay} à {$time}. Vous pourrez récupérer votre agneau après l'abattage.";
    }
}