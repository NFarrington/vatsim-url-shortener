<?php

namespace Tests\Unit\Models;

use App\Models\EmailVerification;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Url;
use App\Models\User;
use App\Services\VatsimService;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use stdClass;
use Tests\TestCase;

class UserTest extends TestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    /** @test */
    function has_one_email_verification()
    {
        $user = create(User::class);
        $expectedVerification = create(EmailVerification::class, ['user_id' => $user->id]);

        $actualVerification = $user->emailVerification;

        $this->assertEquals($expectedVerification->id, $actualVerification->id);
    }

    /** @test */
    function belongs_to_many_organizations()
    {
        $user = create(User::class);
        $organization1 = create(Organization::class);
        $organization2 = create(Organization::class);
        $organization1->users()->newPivot([
            'organization_id' => $organization1->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_OWNER,
        ])->save();
        $organization2->users()->newPivot([
            'organization_id' => $organization2->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->save();

        $actualOrganizationIds = $user->organizations->pluck('id');

        $this->assertEquals(2, $user->organizations->count());
        $this->assertContains($organization1->id, $actualOrganizationIds);
        $this->assertContains($organization2->id, $actualOrganizationIds);
    }

    /** @test */
    function has_many_urls()
    {
        $user = create(User::class);
        $url1 = create(Url::class, ['user_id' => $user->id]);
        $url2 = create(Url::class, ['user_id' => $user->id]);

        $actualUrlIds = $user->urls->pluck('id');

        $this->assertCount(2, $actualUrlIds);
        $this->assertContains($url1->id, $actualUrlIds);
        $this->assertContains($url2->id, $actualUrlIds);
    }

    /** @test */
    function user_is_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => [$user->id]]);

        $isAdmin = $user->isAdmin();

        $this->assertTrue($isAdmin);
    }

    /** @test */
    function user_is_not_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => []]);

        $isAdmin = $user->isAdmin();

        $this->assertFalse($isAdmin);
    }

    /** @test */
    function provides_full_name()
    {
        $user = create(User::class);

        $fullName = $user->full_name;

        $this->assertEquals("{$user->first_name} {$user->last_name}", $fullName);
    }

    /** @test */
    function provides_display_info()
    {
        $user = create(User::class);

        $displayInfo = $user->display_info;

        $this->assertEquals("{$user->first_name} {$user->last_name} ({$user->id})", $displayInfo);
    }

    /** @test */
    function user_can_be_created_from_cert()
    {
        $userTemplate = make(User::class);
        $vatsimService = $this->createMock(VatsimService::class);
        $vatsimService->method('getUser')->willReturn([
            'id' => $userTemplate->id,
            'name_first' => $userTemplate->first_name,
            'name_last' => $userTemplate->last_name,
        ]);
        $this->app->instance(VatsimService::class, $vatsimService);

        $user = User::createFromCert($userTemplate->id);

        $this->assertArraySubset([
            'id' => $userTemplate->id,
            'first_name' => $userTemplate->first_name,
            'last_name' => $userTemplate->last_name,
        ], $user->attributesToArray());
    }

    /** @test */
    function vatsim_sso_data_changes_are_tracked()
    {
        $ssoData = new stdClass;
        $ssoData->key = 'value';
        $user = create(User::class);

        $user->fresh()->update(['vatsim_sso_data' => $ssoData]);

        $this->assertDatabaseHas('revisions', [
            'model_id' => $user->id,
            'key' => 'vatsim_sso_data',
            'old_value' => null,
            'new_value' => '{"key":"value"}',
        ]);
    }
}
