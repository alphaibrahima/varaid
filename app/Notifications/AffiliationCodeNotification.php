<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class AffiliationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        // return ['mail', 'twilio']; // Utilise Twilio au lieu de Vonage
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre code d\'affiliation')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Merci de vous être inscrit sur notre plateforme.')
            ->line('Votre code d\'affiliation est: ' . $notifiable->affiliation_code)
            ->line('Vous devrez fournir ce code lors de votre première réservation.')
            ->line('Si vous ne présentez pas ce code, vous ne pourrez pas effectuer de réservation.')
            ->action('Accéder à la plateforme', url('/'))
            ->line('Merci de votre confiance!');
    }

    /**
     * Méthode pour envoyer un SMS avec Twilio
     */
    public function toTwilio($notifiable)
    {
        // Vérifier si le numéro de téléphone existe
        if (!$notifiable->phone) {
            \Log::warning('Impossible d\'envoyer un SMS: numéro de téléphone manquant', [
                'user_id' => $notifiable->id,
                'email' => $notifiable->email
            ]);
            return;
        }
        
        try {
            $client = new Client(
                config('services.twilio.sid'), 
                config('services.twilio.token')
            );
            
            $client->messages->create(
                $notifiable->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => 'Votre code d\'affiliation pour la plateforme de réservation est: ' . $notifiable->affiliation_code
                ]
            );
            
            \Log::info('SMS envoyé avec succès', [
                'user_id' => $notifiable->id,
                'phone' => $notifiable->phone
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du SMS', [
                'user_id' => $notifiable->id,
                'phone' => $notifiable->phone,
                'error' => $e->getMessage()
            ]);
        }
    }
}