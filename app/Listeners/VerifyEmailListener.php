<?php

namespace App\Listeners;

use App\Events\EmailChangedEvent;
use App\Models\EmailVerification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VerifyEmailListener
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
     * @param  EmailChangedEvent $event
     * @return void
     */
    public function handle(EmailChangedEvent $event)
    {
        $user = $event->user;

        $key = app_key();
        $user->email_verified = false;
        $user->save();
        $token = hash_hmac('sha256', Str::random(40), $key);
        $verification = $user->emailVerification ?: new EmailVerification();
        $verification->token = Hash::make($token);
        $verification->user_id = $user->id;
        $verification->save();
        $user->notify(new VerifyEmailNotification($token));
    }
}
