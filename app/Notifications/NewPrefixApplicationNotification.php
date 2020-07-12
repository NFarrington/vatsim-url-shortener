<?php

namespace App\Notifications;

use App\Entities\OrganizationPrefixApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewPrefixApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OrganizationPrefixApplication $application;

    public function __construct(OrganizationPrefixApplication $application)
    {
        $this->application = $application;
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
        $appName = config('app.name');

        return (new MailMessage())
            ->subject("$appName - New Prefix Application")
            ->line("A new prefix application has been submitted for {$this->application->getOrganization()->getName()}.");
    }
}
