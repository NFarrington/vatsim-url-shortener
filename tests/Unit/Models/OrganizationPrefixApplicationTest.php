<?php

namespace Tests\Unit\Models;

use App\Models\Organization;
use App\Models\OrganizationPrefixApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationPrefixApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function belongs_to_organization()
    {
        $expectedOrganization = create(Organization::class);
        $application = create(OrganizationPrefixApplication::class, ['organization_id' => $expectedOrganization->id]);

        $actualOrganization = $application->organization;

        $this->assertEquals($expectedOrganization->id, $actualOrganization->id);
    }
}
