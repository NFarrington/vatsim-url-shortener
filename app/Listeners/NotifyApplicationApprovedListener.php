<?php

namespace App\Listeners;

use App\Events\PrefixApplicationApprovedEvent;
use App\Notifications\PrefixApplicationApprovedNotification;
use Illuminate\Support\Facades\Notification;

class NotifyApplicationApprovedListener
{
    public function handle(PrefixApplicationApprovedEvent $event)
    {
        Notification::send(
            $event->prefixApplication->getUser(),
            new PrefixApplicationApprovedNotification(
                $event->prefixApplication->getUser()->getFirstName(),
                $event->prefixApplication->getOrganization()->getName(),
                $event->prefix,
            )
        );
    }
}
