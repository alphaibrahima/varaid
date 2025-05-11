<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssociationCredentialsNotification extends Notification
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Vos identifiants de connexion')
                    ->line('Votre compte association a été créé avec succès.')
                    ->line('Identifiants :')
                    ->line('Email : ' . $notifiable->email)
                    ->line('Mot de passe temporaire : ' . $this->password)
                    ->action('Se connecter', url('/login'))
                    ->line('Changez votre mot de passe après la première connexion.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
