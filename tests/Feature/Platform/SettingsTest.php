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

    #[\PHPUnit\Framework\Attributes\Test]
    function settings_edit_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function two_factor_auth_configuration_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.settings.two-factor'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_configure_two_factor_auth()
    {
        $mock = $this->createStub(Google2FA::class);
        $mock->method('generateSecretKey')->willReturn('SECRETSECRETSECR');
        $mock->method('verifyKeyNewer')->willReturn(58000000);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $this->user->getId(), 'totp_timestamp' => 58000000]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_remove_two_factor_auth()
    {
        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(58000001);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn($user = create(User::class, ['totpSecret' => Str::random(16), 'totpTimestamp' => 58000000]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings'));
        $this->delete(route('platform.settings.two-factor'), ['code' => '123456'])
            ->assertRedirect(route('platform.settings'))
            ->assertSessionHas('success')
            ->assertSessionMissing(['auth.two-factor', 'totp-secret']);
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $user->getId(), 'totp_secret' => null, 'totp_timestamp' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_remove_two_factor_auth_with_an_invalid_code()
    {
        $this->withExceptionHandling();

        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn($user = create(User::class, ['totpSecret' => Str::random(16), 'totpTimestamp' => 58000000]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings'));
        $this->delete(route('platform.settings.two-factor'), ['code' => '123456'])
            ->assertSessionHasErrors('code');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $user->getId(), 'totp_secret' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_remove_two_factor_auth_without_a_code()
    {
        $this->withExceptionHandling();

        $this->signIn($user = create(User::class, ['totpSecret' => Str::random(16)]));
        Session::put('auth.two-factor', new Carbon());

        $this->get(route('platform.settings'));
        $this->delete(route('platform.settings.two-factor'))
            ->assertSessionHasErrors('code');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(User::class)->getTableName(), ['id' => $user->getId(), 'totp_secret' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function two_factor_auth_configuration_without_loading_the_setup_page_fails_validation()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $this->post(route('platform.settings.two-factor'), ['code' => '123456'])
            ->assertSessionHasErrors('code');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_configure_two_factor_auth_with_an_invalid_code()
    {
        $this->withExceptionHandling();

        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertRedirect(route('platform.settings.two-factor'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function two_factor_auth_configuration_attempts_are_rate_limited()
    {
        $this->withExceptionHandling();

        $mock = $this->createStub(Google2FA::class);
        $mock->method('verifyKeyNewer')->willReturn(false);
        $this->app->instance(Google2FA::class, $mock);

        $this->signIn();

        $this->get(route('platform.settings.two-factor'));
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
                ->assertSessionHasErrors('code');
        }
        $this->post(route('platform.settings.two-factor'), ['code' => mt_rand(0, 999999)])
            ->assertStatus(429);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
