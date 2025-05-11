<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AccountCreatedSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $roleName = $notifiable->role === 'association' ? 'association' : 'acheteur';
        
        // return "VARAID: Votre compte {$roleName} a été créé avec succès. Consultez votre boîte mail pour récupérer vos identifiants de connexion.";
        return "Salam Aleykom,\n\nNous vous avons envoyé par mail vos identifiants pour vous connecter sur la plateforme de réservations en ligne : varaid.org\n\nMerci de consulter votre boîte mail (y compris vos mails indésirables).\n\nPour toute difficulté, contactez-nous par mail à varaid.contact@gmail.com.\n\nL’association Varaid\nSite abattage Hyères";

    }
}