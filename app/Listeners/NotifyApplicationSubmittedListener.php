<?php

namespace App\Listeners;

use App\Events\PrefixApplicationCreatedEvent;
use App\Notifications\NewPrefixApplicationNotification;
use Illuminate\Support\Facades\Notification;

class NotifyApplicationSubmittedListener
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
     * @param \App\Events\PrefixApplicationCreatedEvent $event
     * @return void
     */
    public function handle(PrefixApplicationCreatedEvent $event)
    {
        Notification::route('mail', 'support@vats.im')
            ->notify(new NewPrefixApplicationNotification($event->application));
    }
}
