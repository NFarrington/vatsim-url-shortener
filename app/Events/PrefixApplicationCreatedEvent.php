<?php

namespace App\Events;

use App\Models\OrganizationPrefixApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrefixApplicationCreatedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The application.
     *
     * @var \App\Models\User
     */
    public $application;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\OrganizationPrefixApplication $application
     * @return void
     */
    public function __construct(OrganizationPrefixApplication $application)
    {
        $this->application = $application;
    }
}
