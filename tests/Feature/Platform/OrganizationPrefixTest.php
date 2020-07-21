<?php

namespace Tests\Feature\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationPrefixApplication;
use App\Entities\OrganizationUser;
use App\Notifications\NewPrefixApplicationNotification;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;

class OrganizationPrefixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function create_page_loads_successfully()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.organizations.prefix.create', $organization->getId()))
            ->assertStatus(200);
    }

    /** @test */
    function application_can_be_created()
    {
        $this->expectsNotification(
            Notification::route('mail', 'support@vats.im'),
            NewPrefixApplicationNotification::class
        );

        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        $application = make(OrganizationPrefixApplication::class);

        $this->get(route('platform.organizations.prefix.create', $organization));
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $application->getIdentityUrl(),
            'prefix' => $application->getPrefix(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($application))->getTableName(), [
            'identity_url' => $application->getIdentityUrl(),
            'prefix' => $application->getPrefix(),
        ]);
    }

    /** @test */
    function organization_with_prefix_cannot_access_application_pages()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $application = make(OrganizationPrefixApplication::class);

        $this->get(route('platform.organizations.prefix.create', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $application->getIdentityUrl(),
            'prefix' => $application->getPrefix(),
        ])->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(get_class($application))->getTableName(), [
            'identity_url' => $application->getIdentityUrl(),
            'prefix' => $application->getPrefix(),
        ]);
    }

    /** @test */
    function organization_with_prefix_application_cannot_access_application_pages()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        $application = create(OrganizationPrefixApplication::class, ['organization' => $organization]);
        $applicationTemplate = make(OrganizationPrefixApplication::class, ['organization' => $organization]);
        EntityManager::clear();

        $this->get(route('platform.organizations.prefix.create', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $applicationTemplate->getIdentityUrl(),
            'prefix' => $applicationTemplate->getPrefix(),
        ])->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(get_class($applicationTemplate))->getTableName(), [
            'identity_url' => $applicationTemplate->getIdentityUrl(),
            'prefix' => $applicationTemplate->getPrefix(),
        ]);
    }
}
