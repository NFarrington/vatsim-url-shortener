<?php

namespace App\Events;

use App\Entities\OrganizationPrefixApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrefixApplicationRejectedEvent
{
    use Dispatchable, SerializesModels;

    public OrganizationPrefixApplication $prefixApplication;
    public string $reason;

    public function __construct(OrganizationPrefixApplication $prefixApplication, string $reason)
    {
        $this->prefixApplication = $prefixApplication;
        $this->reason = $reason;
    }
}
