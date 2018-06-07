<?php

namespace App\Providers;

use App\Events\EmailChangedEvent;
use App\Events\EmailVerifiedEvent;
use App\Events\PrefixApplicationCreatedEvent;
use App\Listeners\DeleteEmailVerificationListener;
use App\Listeners\NotifyApplicationSubmittedListener;
use App\Listeners\VerifyEmailListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EmailChangedEvent::class => [
            VerifyEmailListener::class,
        ],

        EmailVerifiedEvent::class => [
            DeleteEmailVerificationListener::class,
        ],

        PrefixApplicationCreatedEvent::class => [
            NotifyApplicationSubmittedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
