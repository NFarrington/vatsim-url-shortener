<?php

namespace App\Events;

use App\Entities\OrganizationPrefixApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrefixApplicationCreatedEvent
{
    use Dispatchable, SerializesModels;

    public OrganizationPrefixApplication $application;

    public function __construct(OrganizationPrefixApplication $application)
    {
        $this->application = $application;
    }
}
