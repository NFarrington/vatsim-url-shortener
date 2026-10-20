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

    #[\PHPUnit\Framework\Attributes\Test]
    function create_page_loads_successfully()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.organizations.prefix.create', $organization->getId()))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_can_be_created()
    {
        Notification::fake();

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
        Notification::assertSentOnDemand(
            NewPrefixApplicationNotification::class,
            fn($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'support@vats.im'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['taken'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['TAKEN'])]
    function application_cannot_use_a_prefix_another_organization_has($prefix)
    {
        $this->withExceptionHandling();

        create(Organization::class, ['prefix' => 'taken']);
        $organization = $this->createOwnedOrganization();

        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => 'https://example.com',
            'prefix' => $prefix,
        ])->assertSessionHasErrors('prefix');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(OrganizationPrefixApplication::class)->getTableName(), [
            'prefix' => $prefix,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['platform'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['Assets'])]
    function application_cannot_use_a_reserved_prefix($prefix)
    {
        $this->withExceptionHandling();

        $organization = $this->createOwnedOrganization();

        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => 'https://example.com',
            'prefix' => $prefix,
        ])->assertSessionHasErrors('prefix');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['ftp://example.com/identity'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['file://localhost/etc/passwd'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['view-source://example.com'])]
    function application_identity_url_must_use_http_or_https($identityUrl)
    {
        $this->withExceptionHandling();

        $organization = $this->createOwnedOrganization();

        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $identityUrl,
            'prefix' => 'pfx',
        ])->assertSessionHasErrors('identity_url');
    }

    protected function createOwnedOrganization(): Organization
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        return $organization;
    }
}
