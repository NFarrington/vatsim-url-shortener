<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\Domain;
use App\Entities\Organization;
use App\Entities\Url;
use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use App\Services\VatsimService;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Mockery;
use Mockery\MockInterface;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_listed_with_their_owners()
    {
        $this->signInAdmin();
        $userUrl = create(Url::class);
        $organizationUrl = entity(Url::class)->states('org')->create();
        $unownedUrl = create(Url::class, ['user' => null]);
        EntityManager::clear();

        $this->get(route('platform.admin.urls.index'))
            ->assertStatus(200)
            ->assertSeeInOrder([$userUrl->getUrl(), $userUrl->getUser()->getDisplayInfo()])
            ->assertSeeInOrder([$organizationUrl->getUrl(), $organizationUrl->getOrganization()->getName()])
            ->assertSeeInOrder([$unownedUrl->getUrl(), '&mdash;', route('platform.admin.urls.edit', $unownedUrl)], false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_searched_by_short_url()
    {
        $this->signInAdmin();
        $url = create(Url::class, ['url' => 'FindMe']);
        $otherUrl = create(Url::class, ['url' => 'other']);

        $this->get(route('platform.admin.urls.index', ['q' => 'findme']))
            ->assertStatus(200)
            ->assertSee($url->getRedirectUrl())
            ->assertDontSee($otherUrl->getRedirectUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_searched_by_full_prefixed_url()
    {
        $this->signInAdmin();
        $organization = create(Organization::class, ['prefix' => 'abc']);
        $url = entity(Url::class)->states('prefix')->create(['organization' => $organization, 'user' => null, 'url' => 'events']);
        $otherUrl = create(Url::class, ['url' => 'events2']);

        $search = preg_replace('#^https?://#', '', $url->getDomain()->getUrl()).'abc/events';
        $this->get(route('platform.admin.urls.index', ['q' => $search]))
            ->assertStatus(200)
            ->assertSee($url->getRedirectUrl())
            ->assertDontSee($otherUrl->getRedirectUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_searched_by_redirect_url()
    {
        $this->signInAdmin();
        $url = create(Url::class, ['redirectUrl' => 'https://example.com/Needle']);
        $otherUrl = create(Url::class);

        $this->get(route('platform.admin.urls.index', ['q' => 'needle']))
            ->assertStatus(200)
            ->assertSee($url->getUrl())
            ->assertDontSee($otherUrl->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_searched_by_owner_name_or_cid()
    {
        $this->signInAdmin();
        $user = create(User::class, ['firstName' => 'Zebedee', 'lastName' => 'Smith']);
        $userUrl = create(Url::class, ['user' => $user]);
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);
        $organizationUrl = create(Url::class, ['organization' => $organization, 'user' => null]);
        $otherUrl = create(Url::class);

        $this->get(route('platform.admin.urls.index', ['q' => 'zebedee smith']))
            ->assertStatus(200)
            ->assertSee($userUrl->getUrl())
            ->assertDontSee($organizationUrl->getUrl())
            ->assertDontSee($otherUrl->getUrl());
        $this->get(route('platform.admin.urls.index', ['q' => (string) $user->getId()]))
            ->assertStatus(200)
            ->assertSee($userUrl->getUrl())
            ->assertDontSee($otherUrl->getUrl());
        $this->get(route('platform.admin.urls.index', ['q' => 'quokka']))
            ->assertStatus(200)
            ->assertSee($organizationUrl->getUrl())
            ->assertDontSee($userUrl->getUrl())
            ->assertDontSee($otherUrl->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['%'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['_'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['!'])]
    function search_treats_wildcards_literally($search)
    {
        $this->signInAdmin();
        $url = create(Url::class);

        $this->get(route('platform.admin.urls.index', ['q' => $search]))
            ->assertStatus(200)
            ->assertDontSee($url->getUrl())
            ->assertSee('Nothing to show.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function sort_links_keep_the_search()
    {
        $this->signInAdmin();
        create(Url::class, ['url' => 'findme']);

        $this->get(route('platform.admin.urls.index', ['q' => 'findme']))
            ->assertStatus(200)
            ->assertSee('q=findme', false)
            ->assertSee('sort=owner', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function search_keeps_the_sort_order()
    {
        $this->signInAdmin();
        create(Url::class, ['url' => 'findme']);

        $this->get(route('platform.admin.urls.index', ['q' => 'findme', 'sort' => 'owner', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSee('<input type="hidden" name="sort" value="owner">', false)
            ->assertSee('<input type="hidden" name="direction" value="desc">', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_sorted_by_owner_with_unowned_urls_last()
    {
        $this->signInAdmin();
        create(Url::class, ['url' => 'url-one', 'user' => create(User::class, ['firstName' => 'Bravo', 'lastName' => 'User'])]);
        create(Url::class, ['url' => 'url-two', 'user' => null]);
        create(Url::class, ['url' => 'url-three', 'user' => null, 'organization' => create(Organization::class, ['name' => 'Charlie'])]);
        create(Url::class, ['url' => 'url-four', 'user' => create(User::class, ['firstName' => 'alpha', 'lastName' => 'User'])]);

        $this->get(route('platform.admin.urls.index', ['sort' => 'owner', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['url-four', 'url-one', 'url-three', 'url-two']);
        $this->get(route('platform.admin.urls.index', ['sort' => 'owner', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['url-three', 'url-one', 'url-four', 'url-two']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_can_be_sorted_by_full_url()
    {
        $this->signInAdmin();
        create(Url::class, ['url' => 'bbb']);
        create(Url::class, ['url' => 'ccc']);
        create(Url::class, ['url' => 'aaa']);

        $this->get(route('platform.admin.urls.index', ['sort' => 'fullUrl', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['aaa', 'bbb', 'ccc']);
        $this->get(route('platform.admin.urls.index', ['sort' => 'fullUrl', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['ccc', 'bbb', 'aaa']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => 'id,u.redirectUrl']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => ['id']]])]
    #[\PHPUnit\Framework\Attributes\TestWith([['direction' => 'desc, u.id']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['q' => ['findme']]])]
    function url_list_ignores_invalid_parameters($query)
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        create(Url::class, ['url' => 'bbb']);
        create(Url::class, ['url' => 'ccc']);
        create(Url::class, ['url' => 'aaa']);

        // The default order is by ID.
        $this->get(route('platform.admin.urls.index', $query))
            ->assertStatus(200)
            ->assertSessionHasNoErrors()
            ->assertSeeInOrder(['bbb', 'ccc', 'aaa']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads()
    {
        $this->signInAdmin();
        $url = create(Url::class);
        EntityManager::clear();

        $this->get(route('platform.admin.urls.edit', $url))
            ->assertStatus(200)
            ->assertSee($url->getRedirectUrl())
            ->assertSee('name="owner_type"', false)
            ->assertSee('value="'.$url->getUser()->getId().'"', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function show_redirects_to_edit()
    {
        $this->signInAdmin();
        $url = create(Url::class);

        $this->get(route('platform.admin.urls.show', $url))
            ->assertRedirect(route('platform.admin.urls.edit', $url));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function redirect_url_can_be_updated()
    {
        $this->signInAdmin();
        $dynamoDb = $this->mockDynamoDb();
        $url = create(Url::class);
        $owner = $url->getUser();

        $dynamoDb->expects('putItem')->with([
            'TableName' => 'TestUrlsTable',
            'Item' => [
                'Url' => ['S' => Str::lower($url->getFullUrl())],
                'RedirectUrl' => ['S' => 'https://example.com/new'],
                'UpdatedAt' => ['S' => Carbon::now()->toIso8601ZuluString()],
            ],
        ]);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => 'https://example.com/new',
            'owner_type' => 'user',
            'user_id' => $owner->getId(),
        ])->assertRedirect(route('platform.admin.urls.index'))
            ->assertSessionHas('success');

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertSame('https://example.com/new', $url->getRedirectUrl());
        $this->assertSame($owner->getId(), $url->getUser()->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_can_be_transferred_to_a_user()
    {
        $this->signInAdmin();
        $url = entity(Url::class)->states('org')->create();
        $newOwner = create(User::class);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'user',
            'user_id' => $newOwner->getId(),
            'organization_id' => $url->getOrganization()->getId(),
        ])->assertRedirect(route('platform.admin.urls.index'));

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertSame($newOwner->getId(), $url->getUser()->getId());
        $this->assertNull($url->getOrganization());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_can_be_transferred_to_an_unregistered_user()
    {
        $this->signInAdmin();
        $url = create(Url::class);
        $newOwner = make(User::class);
        $mock = Mockery::mock(VatsimService::class)->makePartial();
        $mock->expects('createUserFromCert')->with($newOwner->getId())->andReturnUsing(function () use ($newOwner) {
            EntityManager::persist($newOwner);
            EntityManager::flush();

            return $newOwner;
        });
        $this->app->instance(VatsimService::class, $mock);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'user',
            'user_id' => $newOwner->getId(),
        ])->assertRedirect(route('platform.admin.urls.index'));

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertSame($newOwner->getId(), $url->getUser()->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function error_retrieving_unregistered_user_is_shown_against_the_cid()
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $url = create(Url::class);
        $owner = $url->getUser();
        $mock = Mockery::mock(VatsimService::class)->makePartial();
        $mock->allows('getUser')->andThrow(new InvalidResponseException());
        $this->app->instance(VatsimService::class, $mock);

        $this->from(route('platform.admin.urls.edit', $url))
            ->put(route('platform.admin.urls.update', $url), [
                'redirect_url' => $url->getRedirectUrl(),
                'owner_type' => 'user',
                'user_id' => make(User::class)->getId(),
            ])->assertRedirect(route('platform.admin.urls.edit', $url))
            ->assertSessionHasErrors('user_id');

        EntityManager::clear();
        $this->assertSame($owner->getId(), EntityManager::find(Url::class, $url->getId())->getUser()->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_can_be_transferred_to_an_organization()
    {
        $this->signInAdmin();
        $url = create(Url::class);
        $organization = create(Organization::class);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'organization',
            'user_id' => $url->getUser()->getId(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect(route('platform.admin.urls.index'));

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertSame($organization->getId(), $url->getOrganization()->getId());
        $this->assertNull($url->getUser());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_can_be_transferred_to_nobody()
    {
        $this->signInAdmin();
        $url = create(Url::class);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'none',
        ])->assertRedirect(route('platform.admin.urls.index'));

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertNull($url->getUser());
        $this->assertNull($url->getOrganization());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['user', 'user_id'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['organization', 'organization_id'])]
    function transfer_requires_the_new_owner($ownerType, $field)
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $url = create(Url::class, ['user' => null]);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => $ownerType,
        ])->assertSessionHasErrors($field);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function prefixed_urls_cannot_be_transferred()
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $url = entity(Url::class)->states('org', 'prefix')->create();
        $organization = $url->getOrganization();

        $this->get(route('platform.admin.urls.edit', $url))
            ->assertStatus(200)
            ->assertDontSee('name="owner_type" id', false)
            ->assertSee("Prefixed URLs can't be transferred.", false);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'none',
        ])->assertForbidden();

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => 'https://example.com/new',
            'owner_type' => 'organization',
            'organization_id' => $organization->getId(),
        ])->assertRedirect(route('platform.admin.urls.index'));

        EntityManager::clear();
        $url = EntityManager::find(Url::class, $url->getId());
        $this->assertSame('https://example.com/new', $url->getRedirectUrl());
        $this->assertSame($organization->getId(), $url->getOrganization()->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function urls_on_restricted_domains_cannot_be_transferred()
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $domain = entity(Domain::class)->states('private')->create();
        $url = create(Url::class, ['domain' => $domain]);
        EntityManager::clear();

        $this->get(route('platform.admin.urls.edit', $url))
            ->assertStatus(200)
            ->assertSee("URLs on restricted domains can't be transferred.", false);

        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'owner_type' => 'user',
            'user_id' => create(User::class)->getId(),
        ])->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_can_be_deleted()
    {
        $this->signInAdmin();
        $dynamoDb = $this->mockDynamoDb();
        $url = create(Url::class);

        $dynamoDb->expects('deleteItem')->with([
            'TableName' => 'TestUrlsTable',
            'Key' => [
                'Url' => ['S' => Str::lower($url->getFullUrl())],
            ],
        ]);

        $this->delete(route('platform.admin.urls.destroy', $url))
            ->assertRedirect(route('platform.admin.urls.index'))
            ->assertSessionHas('success');

        EntityManager::clear();
        $this->assertNull(EntityManager::find(Url::class, $url->getId()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_page_links_to_url_edit_page()
    {
        $this->signInAdmin();
        $url = entity(Url::class)->states('org')->create();
        EntityManager::clear();

        $this->get(route('platform.admin.organizations.show', $url->getOrganization()))
            ->assertStatus(200)
            ->assertSee(route('platform.admin.urls.edit', $url), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_pages_are_inaccessible_to_non_admins()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $url = create(Url::class, ['user' => $this->user]);

        $this->get(route('platform.admin.urls.index'))
            ->assertForbidden();
        $this->get(route('platform.admin.urls.edit', $url))
            ->assertForbidden();
        $this->put(route('platform.admin.urls.update', $url), [
            'redirect_url' => 'https://example.com/new',
            'owner_type' => 'none',
        ])->assertForbidden();
        $this->delete(route('platform.admin.urls.destroy', $url))
            ->assertForbidden();
    }

    protected function mockDynamoDb(): MockInterface
    {
        Carbon::setTestNow(Carbon::now());
        config(['services.dynamodb.urls_table' => 'TestUrlsTable']);

        $mock = Mockery::mock(DynamoDbClient::class);
        $this->app->instance(DynamoDbClient::class, $mock);

        return $mock;
    }
}
