<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function url_has_user()
    {
        $user = create(User::class);
        $url = create(Url::class, ['user_id' => $user->id]);
        $this->assertEquals($user->id, $url->user->id);
    }

    /** @test */
    function url_has_organization()
    {
        $organization = create(Organization::class);
        $url = create(Url::class, ['organization_id' => $organization->id]);
        $this->assertEquals($organization->id, $url->organization->id);
    }

    /** @test */
    function url_and_redirect_changes_are_tracked()
    {
        $url = create(Url::class);
        $template = make(Url::class);
        (clone $url)->update(['url' => $template->url, 'redirect_url' => $template->redirect_url]);
        $this->assertDatabaseHas('revisions', [
            'model_id' => $url->id,
            'key' => 'url',
            'old_value' => $url->url,
            'new_value' => $template->url,
        ]);
        $this->assertDatabaseHas('revisions', [
            'model_id' => $url->id,
            'key' => 'redirect_url',
            'old_value' => $url->redirect_url,
            'new_value' => $template->redirect_url,
        ]);
    }

    /** @test */
    function urls_without_users_are_public()
    {
        create(Url::class);
        create(Url::class, ['user_id' => null]);

        $this->assertEquals(1, Url::public()->count());
    }

    /** @test */
    function url_has_full_url()
    {
        $url = create(Url::class);
        $this->assertEquals("{$url->domain->url}{$url->url}", $url->full_url);
    }
}
