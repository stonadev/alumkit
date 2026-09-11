<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $reason) {}

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
            ->subject('Your membership application was not approved')
            ->line('We regret to inform you that your membership application has not been approved.')
            ->line("Reason: {$this->reason}")
            ->line('You may reapply after addressing the above concern.');
    }
}
