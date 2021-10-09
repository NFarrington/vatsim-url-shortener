<?php

namespace Tests\Feature\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use App\Services\VatsimService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

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
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::clear();
        $user = create(User::class);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    function unregistered_user_can_be_added_to_organization()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::clear();

        $user = make(User::class);
        $response = /** @lang text */
            <<<EOT
            {"id":"{$user->getId()}","rating":4,"pilotrating":0,"susp_date":null,"reg_date":"2009-04-08T21:51:39","region":"EMEA","division":"GBR","subdivision":" ","lastratingchange":"2014-09-27T11:49:39"}
            EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    function error_retrieving_unregistered_user_causes_validation_exception()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::clear();

        $user = make(User::class);
        $mock = \Mockery::mock(VatsimService::class)->makePartial();
        $mock->allows('getUser')->andThrow(new InvalidResponseException());
        $this->app->instance(VatsimService::class, $mock);

        $this->get(route('platform.organizations.edit', $organization));
        $this->assertThrows(
            ValidationException::class,
            fn() => $this->post(
                route('platform.organizations.users.store', $organization),
                ['id' => $user->getId(), 'role_id' => OrganizationUser::ROLE_MEMBER]
            )
        );
    }

    /** @test */
    function perm_error_retrieving_unregistered_user_causes_validation_exception()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::clear();

        $user = make(User::class);
        $mock = \Mockery::mock(VatsimService::class)->makePartial();
        $mock->allows('getUser')->andThrow(new InvalidResponseException());
        $this->app->instance(VatsimService::class, $mock);

        $this->get(route('platform.organizations.edit', $organization));
        $this->assertThrows(
            ValidationException::class,
            fn() => $this->post(
                route('platform.organizations.users.store', $organization),
                ['id' => $user->getId(), 'role_id' => OrganizationUser::ROLE_MEMBER]
            )
        );
    }

    /** @test */
    function user_can_be_removed_from_organization()
    {
        $user = create(User::class);
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        create(OrganizationUser::class, ['user' => $user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::clear();

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $user]))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSoftDeleted(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $user->getId(),
        ]);
    }

    /** @test */
    function user_cannot_remove_themselves_from_an_organization()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::clear();

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $this->user]))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $this->user->getId(),
            'deleted_at' => null,
        ]);
    }
}
