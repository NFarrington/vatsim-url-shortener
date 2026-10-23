<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\News;
use App\Entities\Organization;
use App\Entities\OrganizationPrefixApplication;
use App\Entities\Url;
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

    #[\PHPUnit\Framework\Attributes\Test]
    function list_flags_applications_for_a_prefix_another_organization_has()
    {
        $this->signInAdmin();
        $owner = create(Organization::class, ['prefix' => 'pfx']);
        create(OrganizationPrefixApplication::class, ['prefix' => 'PFX']);
        create(OrganizationPrefixApplication::class, ['prefix' => 'free']);

        $this->get(route('platform.admin.prefix-applications.index'))
            ->assertStatus(200)
            ->assertSee('Already used by', false)
            ->assertSee('href="'.route('platform.admin.organizations.show', $owner).'"', false);
        $this->assertSame(1, substr_count(
            $this->get(route('platform.admin.prefix-applications.index'))->getContent(),
            'Already used by'
        ));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function review_page_flags_a_prefix_another_organization_has()
    {
        $this->signInAdmin();
        $owner = create(Organization::class, ['prefix' => 'pfx']);
        $application = create(OrganizationPrefixApplication::class, ['prefix' => 'Pfx']);

        $this->get(route('platform.admin.prefix-applications.edit', $application))
            ->assertStatus(200)
            ->assertSee('This prefix is already used by', false)
            ->assertSee('href="'.route('platform.admin.organizations.show', $owner).'"', false)
            ->assertSee(e($owner->getName()), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function review_page_does_not_flag_an_unused_prefix()
    {
        $this->signInAdmin();
        create(Organization::class, ['prefix' => 'other']);
        $application = create(OrganizationPrefixApplication::class, ['prefix' => 'pfx']);

        $this->get(route('platform.admin.prefix-applications.edit', $application))
            ->assertStatus(200)
            ->assertDontSee('This prefix is already used by', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['taken'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['TAKEN'])]
    function application_cannot_be_approved_with_a_prefix_matching_a_root_url($prefix)
    {
        $this->withExceptionHandling();
        Notification::fake();

        $this->signInAdmin();
        create(Url::class, ['url' => 'Taken']);
        $application = create(OrganizationPrefixApplication::class);

        $this->post(route('platform.admin.prefix-applications.approve', $application), [
            'prefix' => $prefix,
        ])->assertSessionHasErrors('prefix');
        Notification::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function application_can_be_approved_with_a_prefix_matching_a_prefixed_url()
    {
        Notification::fake();

        $this->signInAdmin();
        $other = create(Organization::class, ['prefix' => 'other']);
        create(Url::class, ['organization' => $other, 'user' => null, 'prefix' => true, 'url' => 'shared']);
        $application = create(OrganizationPrefixApplication::class);

        $this->post(route('platform.admin.prefix-applications.approve', $application), [
            'prefix' => 'shared',
        ])->assertSessionHasNoErrors();
    }
}
