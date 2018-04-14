<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function two_factor_auth_verification_page_loads_successfully()
    {
        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));

        $this->get(route('login.two-factor'))
            ->assertStatus(200);
    }

    /** @test */
    public function user_is_redirected_to_verification_page_if_unauthenticated()
    {
        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('login.two-factor'));
    }

    /** @test */
    public function user_is_not_redirected_to_verification_page_if_authenticated()
    {
        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }

    /** @test */
    public function user_can_authenticate_successfully()
    {
        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(true);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));

        $this->get(route('login.two-factor'));
        $this->post(route('login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.dashboard'))
            ->assertSessionHas('success');
    }

    /** @test */
    public function user_cannot_authenticate_with_an_invalid_code()
    {
        $this->expectException(ValidationException::class);

        $mock = $this->createMock(Google2FA::class);
        $mock->method('verifyKey')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));

        $this->get(route('login.two-factor'));
        $this->post(route('login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('login.two-factor'))
            ->assertSessionHasErrors('code');
    }

    /** @test */
    public function test_user_cannot_authenticate_if_already_authenticated()
    {
        $this->signIn(create(User::class, ['totp_secret' => str_random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('login.two-factor'))
            ->assertRedirect()->assertSessionHas('error');
        $this->post(route('login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect()->assertSessionHas('error');
    }
}
