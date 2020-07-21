<?php

namespace Tests\Unit\Entities;

use App\Entities\Organization;
use App\Entities\OrganizationPrefixApplication;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

/**
 * @covers \App\Entities\OrganizationPrefixApplication
 */
class OrganizationPrefixApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function belongs_to_organization()
    {
        $expectedOrganization = create(Organization::class);
        $application = create(OrganizationPrefixApplication::class, ['organization' => $expectedOrganization]);
        EntityManager::clear();

        $actualOrganization = EntityManager::find(OrganizationPrefixApplication::class, $application->getId())
            ->getOrganization();

        $this->assertEquals($expectedOrganization->getId(), $actualOrganization->getId());
    }
}
