<?php

namespace Tests\Unit\Models;

use App\Models\Domain;
use App\Models\Organization;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function belongs_to_domain()
    {
        $expectedDomain = create(Domain::class);
        $url = create(Url::class, ['domain_id' => $expectedDomain->id]);

        $actualDomain = $url->domain;

        $this->assertEquals($expectedDomain->id, $actualDomain->id);
    }

    /** @test */
    function belongs_to_organization()
    {
        $expectedOrganization = create(Organization::class);
        $url = create(Url::class, ['organization_id' => $expectedOrganization->id]);

        $actualOrganization = $url->organization;

        $this->assertEquals($expectedOrganization->id, $actualOrganization->id);
    }

    /** @test */
    function belongs_to_user()
    {
        $expectedUser = create(User::class);
        $url = create(Url::class, ['user_id' => $expectedUser->id]);

        $actualUser = $url->user;

        $this->assertEquals($expectedUser->id, $actualUser->id);
    }

    /** @test */
    function provides_full_url()
    {
        $url = create(Url::class);

        $fullUrl = $url->fullUrl;

        $this->assertEquals("{$url->domain->url}{$url->url}", $fullUrl);
    }

    /** @test */
    function provides_full_url_with_prefix()
    {
        $url = factory(Url::class)->states('org', 'prefix')->create();

        $fullUrl = $url->fullUrl;

        $this->assertEquals("{$url->domain->url}{$url->organization->prefix}/{$url->url}", $fullUrl);
    }

    /** @test */
    function urls_without_users_or_organizations_are_public()
    {
        $publicUrl = create(Url::class, ['user_id' => null]);
        $privateUrl = create(Url::class);

        $publicUrlIds = Url::public()->pluck('id');

        $this->assertEquals(1, $publicUrlIds->count());
        $this->assertContains($publicUrl->id, $publicUrlIds);
    }

    /** @test */
    function urls_with_users_are_not_public()
    {
        $privateUrl = create(Url::class);

        $publicUrlIds = Url::public()->pluck('id');

        $this->assertNotContains($privateUrl->id, $publicUrlIds);
    }

    /** @test */
    function urls_with_organizations_are_not_public()
    {
        $privateUrl = factory(Url::class)->states('org')->create();

        $publicUrlIds = Url::public()->pluck('id');

        $this->assertNotContains($privateUrl->id, $publicUrlIds);
    }

    /** @test */
    function sorting_by_url_uses_full_path()
    {
        $domain1 = create(Domain::class, ['url' => 'https://my-domain1.localhost/']);
        $domain2 = create(Domain::class, ['url' => 'https://my-domain2.localhost/']);
        $url1 = create(Url::class, ['domain_id' => $domain2->id, 'url' => 'my-url1']);
        $url2 = create(Url::class, ['domain_id' => $domain1->id, 'url' => 'my-url2']);

        $actualUrlIds = Url::query()->sortable('url')->pluck('urls.id');

        $this->assertCount(2, $actualUrlIds);
        $this->assertEquals($url2->id, $actualUrlIds[0]);
        $this->assertEquals($url1->id, $actualUrlIds[1]);
    }

    /** @test */
    function sorting_by_url_includes_prefixes_in_full_path()
    {
        $domain = create(Domain::class, ['url' => 'https://my-domain.localhost/']);
        $organization1 = create(Organization::class, ['prefix' => 'my-prefix1']);
        $organization2 = create(Organization::class, ['prefix' => 'my-prefix2']);
        $url1 = factory(Url::class)->states(['org', 'prefix'])->create(
            ['domain_id' => $domain->id, 'organization_id' => $organization2->id, 'url' => 'my-url1']);
        $url2 = factory(Url::class)->states(['org', 'prefix'])->create(
            ['domain_id' => $domain->id, 'organization_id' => $organization1->id, 'url' => 'my-url2']);

        $actualUrlIds = Url::query()->sortable('url')->pluck('urls.id');

        $this->assertCount(2, $actualUrlIds);
        $this->assertEquals($url2->id, $actualUrlIds[0]);
        $this->assertEquals($url1->id, $actualUrlIds[1]);
    }

    /** @test */
    function sorting_by_redirect_url_ignores_protocol()
    {
        $url1 = create(Url::class, ['redirect_url' => 'http://my-test.localhost/example2']);
        $url2 = create(Url::class, ['redirect_url' => 'https://my-test.localhost/example1']);

        $actualUrlIds = Url::query()->sortable('redirect_url')->pluck('id');

        $this->assertCount(2, $actualUrlIds);
        $this->assertEquals($url2->id, $actualUrlIds[0]);
        $this->assertEquals($url1->id, $actualUrlIds[1]);
    }

    /** @test */
    function url_and_redirect_changes_are_tracked()
    {
        $originalUrl = create(Url::class);
        $updatedUrlTemplate = make(Url::class);

        (clone $originalUrl)->update([
            'url' => $updatedUrlTemplate->url,
            'redirect_url' => $updatedUrlTemplate->redirect_url,
        ]);

        $this->assertDatabaseHas('revisions', [
            'model_id' => $originalUrl->id,
            'key' => 'url',
            'old_value' => $originalUrl->url,
            'new_value' => $updatedUrlTemplate->url,
        ]);
        $this->assertDatabaseHas('revisions', [
            'model_id' => $originalUrl->id,
            'key' => 'redirect_url',
            'old_value' => $originalUrl->redirect_url,
            'new_value' => $updatedUrlTemplate->redirect_url,
        ]);
    }
}
