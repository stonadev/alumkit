<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Notifications;

use Alumkit\Alumkit\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Membership $membership,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your membership is active')
            ->line('Your membership is now active.')
            ->action('Go to your dashboard', route('alumkit.dashboard'));

        if ($this->membership->ends_at !== null) {
            $mail->line('It runs until '.$this->membership->ends_at->format('d M Y').'.');
        } else {
            $mail->line('You have a lifetime membership.');
        }

        return $mail;
    }
}
