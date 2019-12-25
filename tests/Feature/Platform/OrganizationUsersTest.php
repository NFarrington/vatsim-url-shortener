<?php

namespace Tests\Feature\Platform;

use App\Exceptions\Cert\InvalidResponseException;
use App\Libraries\VatsimService;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrganizationUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function user_can_be_added_to_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $user = create(User::class);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    function unregistered_user_can_be_added_to_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $user = make(User::class);
        $response = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<root><user cid="{$user->id}"><name_last>{$user->last_name}</name_last><name_first>{$user->first_name}</name_first><email>[hidden]@example.com</email><rating>Observer</rating><regdate>2000-01-01 00:00:00</regdate><pilotrating>P1</pilotrating><country>GB</country><region>Europe</region><division>United Kingdom</division><atctime>1.111</atctime><pilottime>1.111</pilottime></user></root>
EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    function error_retrieving_unregistered_user_causes_validation_exception()
    {
        $this->expectException(ValidationException::class);

        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $user = make(User::class);
        $mock = $this->createMock(VatsimService::class);
        $mock->method('getUser')->willThrowException(new InvalidResponseException());
        $this->app->instance(VatsimService::class, $mock);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ]);
    }

    /** @test */
    function perm_error_retrieving_unregistered_user_causes_validation_exception()
    {
        $this->expectException(ValidationException::class);

        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $user = make(User::class);
        $mock = $this->createMock(VatsimService::class);
        $mock->method('getUser')->willThrowException(new Exception());
        $this->app->instance(VatsimService::class, $mock);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ]);
    }

    /** @test */
    function user_can_be_removed_from_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $user = create(User::class);
        $user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $user]))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSoftDeleted($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    function user_cannot_remove_themselves_from_an_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $this->user]))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }
}
