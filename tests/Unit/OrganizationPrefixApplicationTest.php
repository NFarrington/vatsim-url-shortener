<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\OrganizationPrefixApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationPrefixApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function application_has_organization()
    {
        $organization = create(Organization::class);
        $application = create(OrganizationPrefixApplication::class, ['organization_id' => $organization->id]);
        $this->assertEquals($organization->id, $application->organization->id);
    }
}
