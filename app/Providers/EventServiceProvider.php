<?php

namespace App\Providers;

use App\Events\EmailChangedEvent;
use App\Events\EmailVerifiedEvent;
use App\Events\PrefixApplicationCreatedEvent;
use App\Events\UrlRetrieved;
use App\Events\UrlSaved;
use App\Listeners\CacheShortUrl;
use App\Listeners\CacheShortUrlFromQueue;
use App\Listeners\DeleteEmailVerificationListener;
use App\Listeners\NotifyApplicationSubmittedListener;
use App\Listeners\RecordJobProcessingListener;
use App\Listeners\ResolveEntityManager;
use App\Listeners\VerifyEmailListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;

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

        JobProcessing::class => [
            RecordJobProcessingListener::class,

            // ensure the entity manager has been resolved before processing
            // any queued jobs as this will also initialize the autoloader
            // for Doctrine proxies - unserialize() could fail otherwise
            ResolveEntityManager::class,
        ],

        PrefixApplicationCreatedEvent::class => [
            NotifyApplicationSubmittedListener::class,
        ],

        UrlRetrieved::class => [
            CacheShortUrl::class,
        ],

        UrlSaved::class => [
            CacheShortUrlFromQueue::class,
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
