<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\News;
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

    /** @test */
    function applications_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.prefix-applications.index'))
            ->assertStatus(200);
        create(OrganizationPrefixApplication::class);
        $this->get(route('platform.admin.prefix-applications.index'))
            ->assertStatus(200);
    }

    /** @test */
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $application = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.show', $application))
            ->assertRedirect(route('platform.admin.prefix-applications.edit', $application));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $news = create(OrganizationPrefixApplication::class);

        $this->get(route('platform.admin.prefix-applications.edit', $news))
            ->assertStatus(200);
    }

    /** @test */
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

    /** @test */
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
}
