<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;
    protected $pdfPath;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->generatePdf();
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
        // Formater l'heure pour n'afficher que l'heure (sans date)
        $formattedTime = date('H:i', strtotime($this->reservation->slot->start_time));
        
        $mailMessage = (new MailMessage)
            ->subject('Confirmation de votre réservation d\'agneau')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Nous sommes heureux de vous confirmer votre réservation d\'agneau pour l\'Eid.')
            ->line('Numéro de réservation: ' . $this->reservation->code)
            ->line('Date: ' . $this->reservation->slot->date->format('d/m/Y'))
            ->line('Heure: ' . $formattedTime)
            ->line('Quantité: ' . $this->reservation->quantity)
            ->line('Taille: ' . ucfirst($this->reservation->size))
            ->line('Acompte payé: ' . ($this->reservation->quantity * 100) . '€')
            ->line('Le solde de ' . ($this->reservation->quantity * 100) . '€ sera à régler lors de la récupération.');

        // Ajouter les informations des propriétaires
        if (!empty($this->reservation->owners_data)) {
            $owners = json_decode($this->reservation->owners_data);
            
            $mailMessage->line('Informations des propriétaires:');
            
            foreach ($owners as $index => $owner) {
                $mailMessage->line('- Agneau #' . ($index + 1) . ': ' . $owner->firstname . ' ' . $owner->lastname);
            }
        }

        $mailMessage->action('Voir votre réservation', url('/reservation/receipt/' . $this->reservation->code))
            ->line('Merci de nous avoir fait confiance!');
        
        // Ajout de la pièce jointe PDF si elle existe
        if (isset($this->pdfPath) && file_exists($this->pdfPath)) {
            $mailMessage->attach($this->pdfPath, [
                'as' => 'reservation-' . $this->reservation->code . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mailMessage;
    }

    /**
     * Generate PDF receipt
     */
    protected function generatePdf()
    {
        $pdf = PDF::loadView('reservation.receipt-pdf', [
            'reservation' => $this->reservation
        ]);
        
        $path = storage_path('app/receipts/');
        
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        
        $this->pdfPath = $path . 'reservation-' . $this->reservation->code . '.pdf';
        $pdf->save($this->pdfPath);
    }
}

