<?php

namespace Tests\Feature\Platform;

use App\Entities\User;
use Carbon\Carbon;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function two_factor_auth_verification_page_loads_successfully()
    {
        $this->signIn(create(User::class, ['totp_secret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'))
            ->assertStatus(200);
    }

    /** @test */
    function user_is_redirected_to_verification_page_if_unauthenticated()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.login.two-factor'));
    }

    /** @test */
    function user_is_not_redirected_to_verification_page_if_authenticated()
    {
        $this->signIn(create(User::class, ['totp_secret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }

    /** @test */
    function user_can_authenticate_successfully()
    {
        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(true);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totp_secret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.dashboard'))
            ->assertSessionHas('success');
    }

    /** @test */
    function user_cannot_authenticate_with_an_invalid_code()
    {
        $this->withExceptionHandling();

        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totp_secret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.login.two-factor'))
            ->assertSessionHasErrors('code');
    }

    /** @test */
    function test_user_cannot_authenticate_if_already_authenticated()
    {
        $this->signIn(create(User::class, ['totp_secret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.login.two-factor'))
            ->assertRedirect()->assertSessionHas('error');
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect()->assertSessionHas('error');
    }
}
