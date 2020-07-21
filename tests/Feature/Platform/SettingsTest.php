<?php

namespace Tests\Feature\Platform;

use App\Entities\User;
use Carbon\Carbon;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function settings_edit_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings'))
            ->assertStatus(200);
    }

    /** @test */
    function user_can_change_their_email()
    {
        $this->signIn();

        $user = make(User::class);

        $this->get(route('platform.settings'));
        $this->put(route('platform.settings', ['email' => $user->getEmail()]))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(), ['email' => $user->getEmail(), 'email_verified' => 0]);
    }

    /** @test */
    function user_cannot_use_an_existing_email()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $email = create(User::class)->getEmail();

        $this->get(route('platform.settings'));
        $this->put(route('platform.settings', ['email' => $email]))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHasErrors('email');
    }

    /** @test */
    function two_factor_auth_configuration_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings.two-factor'))
            ->assertStatus(200);
    }

    /** @test */
    function user_can_configure_two_factor_auth()
    {
        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(true);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success');
    }

    /** @test */
    function user_can_remove_two_factor_auth()
    {
        $this->signIn($user = create(User::class, ['totpSecret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings'));
        $this->delete(route('platform.settings.two-factor'))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success')
            ->assertSessionMissing(['auth.two-factor', 'totp-secret']);
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $user->getId(), 'totp_secret' => null]);
    }

    /** @test */
    function user_cannot_configure_two_factor_auth_with_an_invalid_code()
    {
        $this->withExceptionHandling();

        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.settings.two-factor'));
    }

    /** @test */
    function user_cannot_configure_two_factor_auth_if_already_configured()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings.two-factor'))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
