<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The token used to verify the email address.
     */
    protected string $token;
    private string $newEmail;
    private ?string $oldEmail;

    public function __construct(string $token, string $newEmail, ?string $oldEmail = null)
    {
        $this->token = $token;
        $this->newEmail = $newEmail;
        $this->oldEmail = $oldEmail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
            ->subject("$appName - Verify Email Address")
            ->line('Please click the button below to verify your email address:')
            ->action('Verify Email Address', route('platform.register.verify', $this->token));
    }
}
