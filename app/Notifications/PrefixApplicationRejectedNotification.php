<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PrefixApplicationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $organizationName;
    protected string $reason;

    public function __construct(string $organizationName, string $reason)
    {
        $this->organizationName = $organizationName;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('VATS.IM Prefix Application - Status Update')
            ->markdown('mail.prefix-application.rejected', [
                'name' => $notifiable->getfirstName(),
                'organizationName' => $this->organizationName,
                'reason' => $this->reason,
            ]);
    }
}
