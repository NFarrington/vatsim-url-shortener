<?php

namespace App\Listeners;

use App\Entities\EmailVerification;
use App\Events\EmailChangedEvent;
use App\Notifications\VerifyEmailNotification;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class VerifyEmailListener
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Handle the event.
     *
     * @param  EmailChangedEvent $event
     * @return void
     */
    public function handle(EmailChangedEvent $event)
    {
        $user = $event->user;

        $key = app_key();
        $user->setEmailVerified(false);
        $token = hash_hmac('sha256', Str::random(40), $key);
        $verification = $user->getEmailVerification() ?: new EmailVerification();
        $verification->setToken(Hash::make($token));
        $verification->setUser($user);
        $this->em->flush();
        Notification::send($user, new VerifyEmailNotification($token, $event->newEmail, $event->oldEmail));
    }
}
