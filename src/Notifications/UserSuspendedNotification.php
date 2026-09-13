<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $reason) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been suspended')
            ->line('Your account has been suspended.')
            ->line("Reason: {$this->reason}")
            ->line('Contact support if you believe this is an error.');
    }
}
