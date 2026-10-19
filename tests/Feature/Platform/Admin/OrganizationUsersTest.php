<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

class OrganizationUsersTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_be_added_to_organization()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        $user = create(User::class);
        EntityManager::clear();

        $this->post(route('platform.admin.organizations.users.store', $organization), [
            'id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MANAGER,
        ])->assertRedirect(route('platform.admin.organizations.show', $organization))
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MANAGER,
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function unregistered_user_can_be_added_to_organization()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        EntityManager::clear();

        $user = make(User::class);
        $response = /** @lang text */
            <<<EOT
            {"id":"{$user->getId()}","rating":4,"pilotrating":0,"susp_date":null,"reg_date":"2009-04-08T21:51:39","region":"EMEA","division":"GBR","subdivision":" ","lastratingchange":"2014-09-27T11:49:39"}
            EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->app->instance('guzzle', $client);

        $this->post(route('platform.admin.organizations.users.store', $organization), [
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

    #[\PHPUnit\Framework\Attributes\Test]
    function existing_member_cannot_be_added_again()
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $organizationUser = create(OrganizationUser::class);
        $organization = $organizationUser->getOrganization();
        EntityManager::clear();

        $this->post(route('platform.admin.organizations.users.store', $organization), [
            'id' => $organizationUser->getUser()->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertSessionHasErrors('id');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_be_removed_from_organization()
    {
        $this->signInAdmin();
        $organizationUser = create(OrganizationUser::class, ['roleId' => OrganizationUser::ROLE_OWNER]);
        $organization = $organizationUser->getOrganization();
        $user = $organizationUser->getUser();
        EntityManager::clear();

        $this->delete(route('platform.admin.organizations.users.destroy', [$organization, $user]))
            ->assertRedirect(route('platform.admin.organizations.show', $organization))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(OrganizationUser::class)->getTableName(), [
            'organization_id' => $organization->getId(),
            'user_id' => $user->getId(),
            'deleted_at' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function removing_a_non_member_shows_an_error()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        $user = create(User::class);
        EntityManager::clear();

        $this->delete(route('platform.admin.organizations.users.destroy', [$organization, $user]))
            ->assertRedirect(route('platform.admin.organizations.show', $organization))
            ->assertSessionHas('error');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function members_cannot_be_managed_by_non_admins()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $organizationUser = create(OrganizationUser::class);
        $organization = $organizationUser->getOrganization();
        $user = create(User::class);

        $this->post(route('platform.admin.organizations.users.store', $organization), [
            'id' => $user->getId(),
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertForbidden();
        $this->delete(route('platform.admin.organizations.users.destroy', [$organization, $organizationUser->getUser()]))
            ->assertForbidden();
    }
}
