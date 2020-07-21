<?php

declare(strict_types=1);

namespace App\Entities\Traits;

use App\Exceptions\UnsupportedException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

trait RoutesNotifications
{
    public function routeNotificationFor(string $driver, Notification $notification = null)
    {
        if (method_exists($this, $method = 'routeNotificationFor'.Str::studly($driver))) {
            return $this->{$method}($notification);
        }

        switch ($driver) {
            case 'mail':
                return $this->email;
            default:
                throw new UnsupportedException("Unsupported notification driver: $driver");
        }
    }

    // required by Illuminate\Support\Testing\Fakes\NotificationFake
    public function getKey(): int
    {
        return $this->id;
    }
}
