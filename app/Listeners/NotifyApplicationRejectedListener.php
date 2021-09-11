<?php

namespace App\Listeners;

use App\Events\PrefixApplicationRejectedEvent;
use App\Notifications\PrefixApplicationRejectedNotification;
use Illuminate\Support\Facades\Notification;

class NotifyApplicationRejectedListener
{
    public function handle(PrefixApplicationRejectedEvent $event)
    {
        Notification::send(
            $event->prefixApplication->getUser(),
            new PrefixApplicationRejectedNotification(
                $event->prefixApplication->getOrganization()->getName(),
                $event->reason,
            )
        );
    }
}
