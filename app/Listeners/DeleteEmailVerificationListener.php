<?php

namespace App\Listeners;

use App\Events\EmailVerifiedEvent;

class DeleteEmailVerificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\EmailVerifiedEvent $event
     * @return void
     * @throws \Exception
     */
    public function handle(EmailVerifiedEvent $event)
    {
        if ($verification = $event->user->emailVerification) {
            $verification->delete();
        }
    }
}
