<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class SelectionReminderSmsNotification extends Notification implements ShouldQueue
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
        // N'envoyer que si l'utilisateur n'a pas choisi de sauter la sélection
        if ($this->reservation->skip_selection) {
            return [];
        }
        
        return ['brevo_sms'];
    }

    /**
     * Get the Brevo SMS representation of the notification.
     */
    public function toBrevoSms($notifiable)
    {
        // Formater la date
        $date = $this->reservation->date->format('d/m/Y');
        
        // Formater l'heure
        $time = $this->reservation->slot->start_time;
        if (is_string($time)) {
            $time = substr($time, 0, 5);
        } elseif (is_object($time) && method_exists($time, 'format')) {
            $time = $time->format('H:i');
        }
        
        // Adresse du site
        $address = "site d'abattage de Hyères";
        
        return "VARAID: RAPPEL! Votre RDV pour choisir votre agneau est demain {$date} à {$time} ({$address}). N'oubliez pas votre reçu #{$this->reservation->code}.";
    }
}