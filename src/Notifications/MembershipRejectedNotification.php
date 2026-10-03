<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Notifications;

use Alumkit\Alumkit\Models\MembershipPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MembershipPayment $payment,
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
            ->subject('Your membership payment was not approved')
            ->line('We could not approve the membership payment you submitted.')
            ->action('Go to your dashboard', route('alumkit.dashboard'));

        if ($this->payment->review_notes !== null) {
            $mail->line('Reason: '.$this->payment->review_notes);
        }

        return $mail;
    }
}
