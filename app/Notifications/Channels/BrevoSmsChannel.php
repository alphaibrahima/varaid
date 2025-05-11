<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoSmsChannel
{
    /**
     * Envoyer le SMS via Brevo
     */
    public function send($notifiable, Notification $notification)
    {
        // Vérifier si la méthode toBrevoSms existe
        if (!method_exists($notification, 'toBrevoSms')) {
            return;
        }

        // Récupérer le numéro de téléphone du notifiable
        $to = $this->getRecipientPhone($notifiable);
        if (empty($to)) {
            Log::warning('No phone number provided for Brevo SMS', ['notifiable' => get_class($notifiable)]);
            return;
        }

        // Récupérer le contenu du message
        $message = $notification->toBrevoSms($notifiable);
        if (empty($message)) {
            Log::warning('Empty message for Brevo SMS', ['notifiable' => get_class($notifiable)]);
            return;
        }

        // Récupérer la clé API
        $apiKey = config('services.brevo.api_key');
        if (empty($apiKey)) {
            Log::error('Brevo API key not configured');
            return;
        }

        // Définir l'expéditeur (sender)
        $sender = config('services.brevo.sender', 'VARAID');

        // Préparer les données pour l'API
        $data = [
            'sender' => $sender,
            'recipient' => $to,
            'content' => $message,
        ];

        // Log les données (pour le débogage)
        Log::info('Attempting to send SMS via Brevo', [
            'to' => $to,
            'sender' => $sender,
            'message_length' => strlen($message),
            'api_key_length' => strlen($apiKey)
        ]);

        try {
            // Envoyer la requête API
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/transactionalSMS/sms', $data);

            // Log la réponse
            Log::info('Brevo API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Vérifier si la requête a réussi
            if (!$response->successful()) {
                Log::error('Failed to send SMS via Brevo', [
                    'to' => $to,
                    'error' => $response->body(),
                    'status' => $response->status()
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS via Brevo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Récupérer le numéro de téléphone du destinataire
     */
    protected function getRecipientPhone($notifiable)
    {
        if (method_exists($notifiable, 'routeNotificationForBrevoSms')) {
            return $notifiable->routeNotificationForBrevoSms();
        }

        // Fallback - utiliser le champ phone si disponible
        if (isset($notifiable->phone)) {
            return $notifiable->phone;
        }

        return null;
    }
}