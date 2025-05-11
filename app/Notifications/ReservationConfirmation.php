<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;
    protected $pdfPath;
    protected $isAdminCreated;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation, bool $isAdminCreated = false)
    {
        $this->reservation = $reservation;
        $this->isAdminCreated = $isAdminCreated;
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
        Log::info('Preparing reservation confirmation email', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $notifiable->id,
            'pdf_generated' => isset($this->pdfPath),
            'is_admin_created' => $this->isAdminCreated
        ]);
        
        try {
            // Formater la date du créneau
            $formattedDate = $this->reservation->date ? $this->reservation->date->format('d/m/Y') : 'Non spécifiée';
            
            // Formater l'heure du créneau correctement
            $formattedTime = 'Non spécifiée';
            if ($this->reservation->slot) {
                if (isset($this->reservation->slot->start_time)) {
                    // Gérer différents formats possibles de l'heure
                    if (is_string($this->reservation->slot->start_time)) {
                        // Si c'est une chaîne, extrait les 5 premiers caractères (HH:MM)
                        $formattedTime = substr($this->reservation->slot->start_time, 0, 5);
                    } elseif (is_object($this->reservation->slot->start_time) && method_exists($this->reservation->slot->start_time, 'format')) {
                        // Si c'est un objet DateTime/Carbon
                        $formattedTime = $this->reservation->slot->start_time->format('H:i');
                    }
                }
            }
            
            // Montant de l'acompte
            $depositAmount = $this->reservation->quantity * 50;
            
            // Préparer le message sur la sélection sur place
            $selectionMessage = $this->reservation->skip_selection 
                ? 'Non (l\'agneau sera attribué par l\'association)' 
                : 'Oui (vous viendrez choisir votre agneau)';
            
            // Construire le message
            $mailMessage = (new MailMessage)
                ->subject('Confirmation de votre réservation d\'agneau pour l\'Aïd');
            
            // Si créée par un admin, ajouter un message spécifique
            if ($this->isAdminCreated) {
                $mailMessage->greeting('Bonjour ' . ($notifiable->firstname ? $notifiable->firstname . ' ' : '') . $notifiable->name)
                    ->line('Une réservation a été créée pour vous par l\'administration de la plateforme.');
            } else {
                $mailMessage->greeting('Bonjour ' . ($notifiable->firstname ? $notifiable->firstname . ' ' : '') . $notifiable->name)
                    ->line('Nous sommes heureux de vous confirmer votre réservation d\'agneau pour l\'Aïd.');
            }
            
            $mailMessage->line('Numéro de réservation: ' . $this->reservation->code)
                ->line('Date: ' . $formattedDate)
                ->line('Heure: ' . $formattedTime);
                
            if ($this->reservation->eid_day) {
                $mailMessage->line('Jour du sacrifice: ' . $this->reservation->eid_day);
            }
            
            $mailMessage->line('Quantité: ' . $this->reservation->quantity)
                ->line('Taille: ' . ucfirst($this->reservation->size))
                ->line('Sélection sur place: ' . $selectionMessage);
                
            // Si c'est une réservation admin, expliquer le paiement différent
            if ($this->isAdminCreated) {
                $mailMessage->line('Cette réservation a été effectuée par l\'administration.');
            } else {
                $mailMessage->line('Acompte payé: ' . $depositAmount . '€')
                    ->line('Le solde sera à régler lors de la récupération.');
            }

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
            
            // Ajouter la pièce jointe PDF si elle existe
            if (isset($this->pdfPath) && file_exists($this->pdfPath)) {
                Log::info('Attaching PDF to email', ['path' => $this->pdfPath]);
                
                $mailMessage->attach($this->pdfPath, [
                    'as' => 'reservation-' . $this->reservation->code . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            } else {
                Log::warning('PDF file not found or not generated', [
                    'reservation_id' => $this->reservation->id,
                    'pdf_path' => $this->pdfPath ?? 'null'
                ]);
            }
            
            return $mailMessage;
        } catch (\Exception $e) {
            Log::error('Error preparing reservation confirmation email', [
                'error' => $e->getMessage(),
                'reservation_id' => $this->reservation->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback simple en cas d'erreur
            return (new MailMessage)
                ->subject('Confirmation de votre réservation')
                ->line('Votre réservation a été confirmée. Code: ' . $this->reservation->code)
                ->action('Voir les détails', route('reservation.receipt', ['code' => $this->reservation->code]));
        }
    }

    /**
     * Generate PDF receipt
     */
    protected function generatePdf()
    {
        try {
            Log::info('Generating PDF for reservation', ['id' => $this->reservation->id]);
            
            // Charger les relations si elles ne sont pas déjà chargées
            if (!$this->reservation->relationLoaded('user')) {
                $this->reservation->load(['user', 'slot', 'association']);
            }
            
            $pdf = PDF::loadView('reservation.receipt-pdf', [
                'reservation' => $this->reservation,
                'isAdminCreated' => $this->isAdminCreated
            ]);
            
            $path = storage_path('app/receipts/');
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $this->pdfPath = $path . 'reservation-' . $this->reservation->code . '.pdf';
            $pdf->save($this->pdfPath);
            
            Log::info('PDF generated successfully', ['path' => $this->pdfPath]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF', [
                'error' => $e->getMessage(),
                'reservation_id' => $this->reservation->id,
                'trace' => $e->getTraceAsString()
            ]);
            $this->pdfPath = null;
        }
    }
}