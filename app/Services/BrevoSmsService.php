<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BrevoSmsService
{
    protected $apiKey;
    protected $sender;

    /**
     * Constructeur du service Brevo SMS
     */
    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key');
        $this->sender = config('services.brevo.sms_sender');
    }

    /**
     * Envoyer un SMS via l'API Brevo
     * 
     * @param string $to Numéro de téléphone du destinataire
     * @param string $message Contenu du message
     * @return bool Succès ou échec de l'envoi
     */
    public function sendSMS($to, $message)
    {
        try {
            // Formatage du numéro de téléphone international
            $to = $this->formatPhoneNumber($to);
            
            // Construire les données de la requête
            $requestData = [
                'sender' => $this->sender,
                'recipient' => $to,
                'content' => $message,
                'type' => 'transactional'
            ];
            
            // Log des données de la requête pour débogage
            Log::info('Attempting to send SMS via Brevo', [
                'to' => $to,
                'sender' => $this->sender,
                'message_length' => strlen($message),
                'api_key_length' => strlen($this->apiKey)
            ]);
            
            // Préparation de l'appel API vers Brevo
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/transactionalSMS/sms', $requestData);
            
            // Log de la réponse complète
            Log::info('Brevo API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);
            
            // Traitement de la réponse
            if ($response->successful()) {
                Log::info('SMS sent successfully via Brevo', [
                    'to' => $to,
                    'message_id' => $response->json('messageId')
                ]);
                return true;
            } else {
                Log::error('Failed to send SMS via Brevo', [
                    'to' => $to,
                    'error' => $response->body(),
                    'status' => $response->status()
                ]);
                return false;
            }
        } catch (Exception $e) {
            Log::error('Exception when sending SMS via Brevo: ' . $e->getMessage(), [
                'to' => $to,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Formater le numéro de téléphone pour le rendre compatible avec Brevo
     * 
     * @param string $phone Numéro de téléphone à formater
     * @return string Numéro de téléphone formaté
     */
    private function formatPhoneNumber($phone)
    {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Formater le numéro français au format international
        if (substr($phone, 0, 2) === '33') {
            // Déjà au format international, on ajoute juste le +
            return '+' . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            // Format local français (commence par 0), on le convertit au format international
            return '+33' . substr($phone, 1);
        } else {
            // Pour les autres cas, on ajoute simplement le +
            return '+' . $phone;
        }
    }
}