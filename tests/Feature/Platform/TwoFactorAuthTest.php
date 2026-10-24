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

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function two_factor_auth_verification_page_loads_successfully()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_is_redirected_to_verification_page_if_unauthenticated()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.login.two-factor'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_is_not_redirected_to_verification_page_if_authenticated()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_authenticate_successfully()
    {
        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(58000000);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn($user = create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.dashboard'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $user->getId(), 'totp_timestamp' => 58000000]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_reuse_a_code()
    {
        $this->withExceptionHandling();

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $code = $google2fa->getCurrentOtp($secret);

        $this->signIn(create(User::class, ['totpSecret' => $secret]));

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => $code])
            ->assertRedirect(route('platform.dashboard'))
            ->assertSessionHas('success');

        // Start a new session, as an attacker who captured the code would.
        Session::flush();

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => $code])
            ->assertRedirect(route('platform.login.two-factor'))
            ->assertSessionHasErrors('code');
        $this->assertFalse(Session::has('auth.two-factor'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function verification_attempts_are_rate_limited()
    {
        $this->withExceptionHandling();

        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'));
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
                ->assertSessionHasErrors('code');
        }
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertStatus(429);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_authenticate_with_an_invalid_code()
    {
        $this->withExceptionHandling();

        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));

        $this->get(route('platform.login.two-factor'));
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.login.two-factor'))
            ->assertSessionHasErrors('code');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_without_two_factor_auth_is_redirected_from_verification_page()
    {
        $this->signIn(create(User::class));

        $this->get(route('platform.login.two-factor'))
            ->assertRedirect(route('platform.dashboard'));
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.dashboard'));
        $this->assertFalse(Session::has('auth.two-factor'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function test_user_cannot_authenticate_if_already_authenticated()
    {
        $this->signIn(create(User::class, ['totpSecret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.login.two-factor'))
            ->assertRedirect()->assertSessionHas('error');
        $this->post(route('platform.login.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect()->assertSessionHas('error');
    }
}
