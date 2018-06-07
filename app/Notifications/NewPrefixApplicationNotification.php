<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewPrefixApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The prefix application.
     *
     * @var \App\Models\OrganizationPrefixApplication
     */
    protected $application;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\OrganizationPrefixApplication $application
     * @return void
     */
    public function __construct($application)
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("$appName - New Prefix Application")
            ->line("A new prefix application has been submitted for {$this->application->organization->name}.");
    }
}
