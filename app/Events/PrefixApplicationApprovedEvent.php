<?php

namespace App\Events;

use App\Entities\OrganizationPrefixApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrefixApplicationApprovedEvent
{
    use Dispatchable, SerializesModels;

    public OrganizationPrefixApplication $prefixApplication;
    public string $prefix;

    public function __construct(OrganizationPrefixApplication $prefixApplication, string $prefix)
    {
        $this->prefixApplication = $prefixApplication;
        $this->prefix = $prefix;
    }
}
