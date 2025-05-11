<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SmsService
{
    /**
     * Envoyer une notification de réservation (méthode existante, adaptée pour Brevo)
     */
    public static function sendReservationNotification($phone, $reservation)
    {
        $message = "Votre réservation est confirmée pour le " . 
                   $reservation->slot->date->format('d/m/Y') . " à " . 
                   $reservation->slot->start_time . ".";
        
        return self::sendSms($phone, $message);
    }

    /**
     * Envoyer un SMS à un numéro spécifique via Brevo
     */
    public static function sendSms($phone, $message)
    {
        $apiKey = config('services.brevo.key');
        $sender = config('services.brevo.sender', 'VARAID');
        $apiUrl = 'https://api.brevo.com/v3/transactionalSMS/sms';

        // Formatage du numéro pour Brevo
        $phone = self::formatPhoneNumber($phone);

        try {
            Log::info('Tentative d\'envoi de SMS via Brevo', [
                'to' => $phone,
                'message' => $message
            ]);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])->post($apiUrl, [
                'sender' => $sender,
                'recipient' => $phone,
                'content' => $message,
                'type' => 'transactional'
            ]);

            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('SMS envoyé avec succès via Brevo', [
                    'to' => $phone,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'message' => 'SMS envoyé avec succès',
                    'data' => $responseData
                ];
            }

            Log::error('Erreur lors de l\'envoi du SMS via Brevo', [
                'to' => $phone,
                'error' => $responseData,
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS',
                'error' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi du SMS via Brevo', [
                'to' => $phone,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer un SMS en masse à plusieurs utilisateurs
     */
    public static function sendBulkSms($message, $userIds = [], $associationId = null, $testMode = false)
    {
        $query = User::whereNotNull('phone');
        
        // Filtrer par association si spécifié
        if ($associationId) {
            $query->where('association_id', $associationId)
                  ->where('role', 'buyer');
        } 
        // Filtrer par IDs spécifiques si fournis
        elseif (!empty($userIds)) {
            $query->whereIn('id', $userIds);
        } 
        // Sinon, tous les acheteurs
        else {
            $query->where('role', 'buyer');
        }
        
        $users = $query->get(['id', 'name', 'phone']);
        
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        // Si en mode test, ne pas envoyer réellement
        if ($testMode) {
            return [
                'success' => true,
                'message' => 'Mode test - Aucun SMS envoyé',
                'recipients' => $users->pluck('phone', 'id')->toArray(),
                'recipients_count' => $users->count(),
                'success_count' => 0,
                'error_count' => 0,
                'details' => []
            ];
        }
        
        // Envoi réel en production
        foreach ($users as $user) {
            if (empty($user->phone)) continue;
            
            $result = self::sendSms($user->phone, $message);
            $results[$user->id] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
            
            // Pause légère pour éviter de surcharger l'API Brevo
            usleep(200000); // 200ms
        }
        
        return [
            'success' => $errorCount === 0,
            'message' => "SMS envoyés: $successCount réussis, $errorCount échecs",
            'recipients' => $users->pluck('phone', 'id')->toArray(),
            'recipients_count' => $users->count(),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'details' => $results
        ];
    }

    /**
     * Formater le numéro de téléphone pour Brevo
     */
    private static function formatPhoneNumber($phone)
    {
        // Retirer tous les caractères non numériques sauf le +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // S'assurer que le numéro commence par + pour format international
        if (!str_starts_with($phone, '+')) {
            // Si commence par 0, remplacer par +33 (pour la France)
            if (str_starts_with($phone, '0')) {
                $phone = '+33' . substr($phone, 1);
            } else {
                // Sinon, ajouter simplement +
                $phone = '+' . $phone;
            }
        }
        
        return $phone;
    }
}