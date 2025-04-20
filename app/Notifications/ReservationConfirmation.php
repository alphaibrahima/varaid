<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ReservationConfirmation extends Notification implements ShouldQueue
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
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info('Preparing reservation confirmation email', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $notifiable->id
        ]);
        
        try {
            // Formater la date du créneau
            $formattedDate = $this->reservation->date ? $this->reservation->date->format('d/m/Y') : 'Non spécifiée';
            
            // Formater l'heure du créneau
            $formattedTime = $this->reservation->slot ? 
                (isset($this->reservation->slot->start_time) ? substr($this->reservation->slot->start_time, 0, 5) : 'Non spécifiée') : 
                'Non spécifiée';
            
            // Montant de l'acompte
            $depositAmount = $this->reservation->quantity * 100;
            
            // Préparer le message sur la sélection sur place
            $selectionMessage = $this->reservation->skip_selection 
                ? 'Non (l\'agneau sera attribué par l\'association)' 
                : 'Oui (vous viendrez choisir votre agneau)';
            
            // Construire le message
            $mailMessage = (new MailMessage)
                ->subject('Confirmation de votre réservation d\'agneau')
                ->greeting('Bonjour ' . $notifiable->name)
                ->line('Nous sommes heureux de vous confirmer votre réservation d\'agneau pour l\'Eid.')
                ->line('Numéro de réservation: ' . $this->reservation->code)
                ->line('Date: ' . $formattedDate)
                ->line('Heure: ' . $formattedTime)
                ->line('Quantité: ' . $this->reservation->quantity)
                ->line('Sélection sur place: ' . $selectionMessage)
                ->line('Acompte payé: ' . $depositAmount . '€')
                ->line('Le solde sera à régler lors de la récupération.');

            // Ajouter les informations des propriétaires si disponibles
            if ($this->reservation->owners_data) {
                $owners = is_string($this->reservation->owners_data) ? 
                    json_decode($this->reservation->owners_data) : 
                    $this->reservation->owners_data;
                
                if (is_array($owners) || is_object($owners)) {
                    $mailMessage->line('Informations des propriétaires:');
                    
                    foreach ($owners as $index => $owner) {
                        if (isset($owner->firstname) && isset($owner->lastname)) {
                            $mailMessage->line('- Agneau #' . ($index + 1) . ': ' . $owner->firstname . ' ' . $owner->lastname);
                        }
                    }
                }
            }

            $mailMessage->action('Voir votre réservation', route('reservation.receipt', ['code' => $this->reservation->code]))
                ->line('Merci de nous avoir fait confiance!');
            
            return $mailMessage;
        } catch (\Exception $e) {
            Log::error('Error preparing reservation confirmation email', [
                'error' => $e->getMessage(),
                'reservation_id' => $this->reservation->id
            ]);
            
            // Fallback simple en cas d'erreur
            return (new MailMessage)
                ->subject('Confirmation de votre réservation')
                ->line('Votre réservation a été confirmée. Code: ' . $this->reservation->code)
                ->action('Voir les détails', route('reservation.receipt', ['code' => $this->reservation->code]));
        }
    }
}