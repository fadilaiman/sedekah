<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Intentionally NOT queued — magic links must be sent immediately.
// Queuing risks silent failure if the queue worker is down,
// and also erodes the 30-minute window before the user even sees the email.
class MagicLinkNotification extends Notification
{

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
