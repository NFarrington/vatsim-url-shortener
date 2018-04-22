<?php

namespace Tests\Unit;

use App\Libraries\Vatsim;
use App\Models\EmailVerification;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use stdClass;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_has_email_verification()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user_id' => $user->id]);
        $this->assertEquals($verification->id, $user->emailVerification->id);
    }

    /** @test */
    function user_has_urls()
    {
        $user = create(User::class);
        $url = create(Url::class, ['user_id' => $user->id]);
        $this->assertEquals($url->id, $user->urls->first()->id);
    }

    /** @test */
    function user_has_organizations()
    {
        $organization = create(Organization::class);
        $user = create(User::class);
        DB::table($organization->users()->getTable())->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_OWNER,
        ]);
        DB::table($organization->users()->getTable())->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ]);
        $this->assertEquals($organization->id, $user->organizations->first()->id);
        $this->assertEquals(2, $user->organizations->count());
    }

    /** @test */
    function user_has_full_name()
    {
        $user = create(User::class);
        $this->assertEquals("{$user->first_name} {$user->last_name}", $user->full_name);
    }

    /** @test */
    function user_has_display_info()
    {
        $user = create(User::class);
        $this->assertEquals("{$user->first_name} {$user->last_name} ({$user->id})", $user->display_info);
    }

    /** @test */
    function user_is_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => [$user->id]]);
        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    function user_is_not_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => []]);
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    function user_can_be_created_from_cert()
    {
        $template = make(User::class);
        $mock = $this->createMock(Vatsim::class);
        $mock->method('getUser')->willReturn([
            'id' => $template->id,
            'name_first' => $template->first_name,
            'name_last' => $template->last_name,
        ]);
        $this->app->instance(Vatsim::class, $mock);
        $user = User::createFromCert($template->id);

        $this->assertArraySubset([
            'id' => $template->id,
            'first_name' => $template->first_name,
            'last_name' => $template->last_name,
        ], $user->attributesToArray());
    }

    /** @test */
    function array_changes_are_tracked()
    {
        $data = new stdClass;
        $data->key = 'value';

        $user = create(User::class);
        $user->fresh()->update(['vatsim_sso_data' => $data]);
        $this->assertDatabaseHas('revisions', [
            'model_id' => $user->id,
            'key' => 'vatsim_sso_data',
            'old_value' => null,
            'new_value' => '{"key":"value"}',
        ]);
    }
}
