<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $word) {}

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
            ->subject("Your account has been {$this->word}")
            ->line("Your account has been {$this->word}.");
    }
}
