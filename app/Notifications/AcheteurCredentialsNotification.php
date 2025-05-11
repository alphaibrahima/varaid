<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcheteurCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    public $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // return ['mail', 'brevo_sms'];
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Vos identifiants de connexion')
                    ->greeting('Bonjour ' . $notifiable->firstname . ' ' . $notifiable->name)
                    ->line('Votre compte a été créé avec succès.')
                    ->line('Voici vos identifiants de connexion:')
                    ->line('Email: ' . $notifiable->email)
                    ->line('Mot de passe temporaire: ' . $this->password)
                    ->action('Se connecter', url('/login'))
                    ->line('Nous vous recommandons de changer votre mot de passe après votre première connexion.');
    }

    // public function toBrevoSms($notifiable)
    // {
    //     return "VARAID: Votre compte acheteur a été créé avec succès. Identifiants: " . 
    //         $notifiable->email . " / " . substr($this->password, 0, 3) . "***. " .
    //         "Consultez votre email pour voir votre mot de passe complet.";
    // }
}