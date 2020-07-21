<?php

namespace App\Listeners;

use App\Events\PrefixApplicationCreatedEvent;
use App\Notifications\NewPrefixApplicationNotification;
use Illuminate\Support\Facades\Notification;

class NotifyApplicationSubmittedListener
{
    public function handle(PrefixApplicationCreatedEvent $event)
    {
        Notification::route('mail', 'support@vats.im')
            ->notify(new NewPrefixApplicationNotification($event->application));
    }
}
