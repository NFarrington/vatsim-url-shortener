<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function settings_edit_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings'))
            ->assertStatus(200);
    }

    /** @test */
    public function user_can_change_their_email()
    {
        $this->signIn();

        $user = make(User::class);

        $this->get(route('platform.settings'));
        $this->put(route('platform.settings', ['email' => $user->email]))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas($user->getTable(), ['email' => $user->email, 'email_verified' => 0]);
    }

    /** @test */
    public function user_cannot_use_an_existing_email()
    {
        $this->expectException(ValidationException::class);

        $this->signIn();

        $email = create(User::class)->email;

        $this->get(route('platform.settings'));
        $this->put(route('platform.settings', ['email' => $email]))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function two_factor_auth_configuration_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings.two-factor'))
            ->assertStatus(200);
    }

    /** @test */
    public function user_can_configure_two_factor_auth()
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
    public function user_can_remove_two_factor_auth()
    {
        $this->signIn($user = create(User::class, ['totp_secret' => str_random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings'));
        $this->delete(route('platform.settings.two-factor'))
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success')
            ->assertSessionMissing(['auth.two-factor', 'totp-secret']);
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id, 'totp_secret' => null]);
    }

    /** @test */
    public function user_cannot_configure_two_factor_auth_with_an_invalid_code()
    {
        $this->expectException(ValidationException::class);

        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.settings.two-factor'));
    }

    /** @test */
    public function user_cannot_configure_two_factor_auth_if_already_configured()
    {
        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings.two-factor'))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
