<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\News;
use App\Entities\Organization;
use App\Entities\OrganizationPrefixApplication;
use App\Notifications\NewPrefixApplicationNotification;
use App\Notifications\PrefixApplicationApprovedNotification;
use App\Notifications\PrefixApplicationRejectedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\Traits\RefreshDatabase;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;

class PrefixApplicationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function applications_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.prefix-applications.index'))
            ->assertStatus(200);
        create(OrganizationPrefixApplication::class);
        $this->get(route('platform.admin.prefix-applications.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $application = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.show', $application))
            ->assertRedirect(route('platform.admin.prefix-applications.edit', $application));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $news = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.edit', $news))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_can_be_approved()
    {
        Notification::fake();

        $this->signInAdmin();
        $application = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.edit', $application));
        $this->post(route('platform.admin.prefix-applications.approve', $application), [
            'prefix' => 'pfx',
        ])->assertRedirect()
            ->assertSessionHas('success');
        Notification::assertSentTo($application->getUser(), PrefixApplicationApprovedNotification::class);
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($application->getOrganization()))->getTableName(), [
            'id' => $application->getOrganization()->getId(),
            'prefix' => 'pfx',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_can_be_rejected()
    {
        Notification::fake();

        $this->signInAdmin();
        $application = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.edit', $application));
        $this->post(route('platform.admin.prefix-applications.reject', $application), [
            'reason' => 'Rejected by test case.',
        ])->assertRedirect()
            ->assertSessionHas('success');
        Notification::assertSentTo($application->getUser(), PrefixApplicationRejectedNotification::class);
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($application->getOrganization()))->getTableName(), [
            'id' => $application->getOrganization()->getId(),
            'prefix' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_cannot_be_approved_with_a_prefix_another_organization_has()
    {
        $this->withExceptionHandling();
        Notification::fake();

        $this->signInAdmin();
        create(Organization::class, ['prefix' => 'pfx']);
        $application = create(OrganizationPrefixApplication::class);

        $this->post(route('platform.admin.prefix-applications.approve', $application), [
            'prefix' => 'PFX',
        ])->assertSessionHasErrors('prefix');
        Notification::assertNothingSent();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Organization::class)->getTableName(), [
            'id' => $application->getOrganization()->getId(),
            'prefix' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_cannot_be_approved_with_a_reserved_prefix()
    {
        $this->withExceptionHandling();

        $this->signInAdmin();
        $application = create(OrganizationPrefixApplication::class);

        $this->post(route('platform.admin.prefix-applications.approve', $application), [
            'prefix' => 'platform',
        ])->assertSessionHasErrors('prefix');
    }
}
