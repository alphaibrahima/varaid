<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationConfirmedSmsNotification extends Notification implements ShouldQueue
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
        // Formater la date
        $date = $this->reservation->date->format('d/m/Y');
        
        // Formater l'heure
        $time = $this->reservation->slot->start_time;
        if (is_string($time)) {
            $time = substr($time, 0, 5);
        } elseif (is_object($time) && method_exists($time, 'format')) {
            $time = $time->format('H:i');
        }
        
        // Récupérer le jour de l'Aïd
        $eidDay = $this->reservation->eid_day;
        
        // Déterminer si l'utilisateur a choisi de venir sélectionner son agneau
        $selectionMessage = $this->reservation->skip_selection 
            ? "Un agneau vous sera attribué par l'association." 
            : "Votre RDV pour le choix: {$date} à {$time}.";
        
        return "VARAID: Réservation #{$this->reservation->code} confirmée. {$selectionMessage} Sacrifice prévu le {$eidDay}. Email envoyé avec les détails.";
    }
}