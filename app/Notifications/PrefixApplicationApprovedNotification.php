<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PrefixApplicationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $firstName;
    protected string $organizationName;
    protected string $prefix;

    public function __construct(string $firstName, string $organizationName, string $prefix)
    {
        $this->firstName = $firstName;
        $this->organizationName = $organizationName;
        $this->prefix = $prefix;
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
            ->markdown('mail.prefix-application.approved', [
                'name' => $this->firstName,
                'organizationName' => $this->organizationName,
                'prefix' => $this->prefix,
            ]);
    }
}
