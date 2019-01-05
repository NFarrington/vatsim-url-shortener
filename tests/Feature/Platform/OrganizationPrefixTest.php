<?php

namespace Tests\Feature\Platform;

use App\Models\Organization;
use App\Models\OrganizationPrefixApplication;
use App\Models\OrganizationUser;
use App\Notifications\NewPrefixApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrganizationPrefixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function create_page_loads_successfully()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $this->get(route('platform.organizations.prefix.create', $organization))
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
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $application = make(OrganizationPrefixApplication::class);

        $this->get(route('platform.organizations.prefix.create', $organization));
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $application->identity_url,
            'prefix' => $application->prefix,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($application->getTable(), [
            'identity_url' => $application->identity_url,
            'prefix' => $application->prefix,
        ]);
    }

    /** @test */
    function organization_with_prefix_cannot_access_application_pages()
    {
        $organization = factory(Organization::class)->states('prefix')->create();
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $application = make(OrganizationPrefixApplication::class);

        $this->get(route('platform.organizations.prefix.create', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $application->identity_url,
            'prefix' => $application->prefix,
        ])->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseMissing($application->getTable(), [
            'identity_url' => $application->identity_url,
            'prefix' => $application->prefix,
        ]);
    }

    /** @test */
    function organization_with_prefix_application_cannot_access_application_pages()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $application = create(OrganizationPrefixApplication::class, ['organization_id' => $organization->id]);
        $applicationTemplate = make(OrganizationPrefixApplication::class, ['organization_id' => $organization->id]);

        $this->get(route('platform.organizations.prefix.create', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.organizations.prefix.store', $organization), [
            'identity_url' => $applicationTemplate->identity_url,
            'prefix' => $applicationTemplate->prefix,
        ])->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseMissing($applicationTemplate->getTable(), [
            'identity_url' => $applicationTemplate->identity_url,
            'prefix' => $applicationTemplate->prefix,
        ]);
    }
}
