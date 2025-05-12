<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\BrevoSmsService;

class BrevoSmsChannel
{
    /**
     * Instance du service Brevo SMS
     */
    protected $smsService;

    /**
     * Créer une nouvelle instance du canal SMS.
     *
     * @param  BrevoSmsService  $smsService
     * @return void
     */
    public function __construct(BrevoSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Envoyer la notification donnée.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Vérifier que la notification possède la méthode nécessaire
        if (!method_exists($notification, 'toBrevoSms')) {
            throw new \Exception('Notification does not have toBrevoSms method');
        }

        // Récupérer le numéro de téléphone du notifiable
        $to = $notifiable->routeNotificationForBrevoSms($notification);

        if (empty($to)) {
            Log::warning('No phone number for notifiable', [
                'notifiable_id' => $notifiable->id ?? 'unknown',
                'notifiable_type' => get_class($notifiable)
            ]);
            return;
        }

        // Récupérer le contenu du message
        $message = $notification->toBrevoSms($notifiable);

        // Envoyer le SMS
        return $this->smsService->sendSMS($to, $message);
    }
}