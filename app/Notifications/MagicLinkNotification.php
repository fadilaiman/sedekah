<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.login.verify', ['token' => $this->token]);

        return (new MailMessage)
            ->subject('Your Sedekah.online Admin Login Link')
            ->greeting('Hello!')
            ->line('Click the button below to log in to the Sedekah.online admin panel.')
            ->line('This link is valid for **30 minutes** and can only be used once.')
            ->action('Log In to Admin Panel', $url)
            ->line('If you did not request this link, you can safely ignore this email.')
            ->salutation('Sedekah.online Team');
    }
}
