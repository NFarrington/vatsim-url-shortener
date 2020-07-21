<?php

namespace Tests\Feature\Platform;

use App\Entities\EmailVerification;
use App\Entities\User;
use App\Notifications\VerifyEmailNotification;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_page_loads_successfully()
    {
        $this->signIn(create(User::class, ['email' => null]));

        $this->get(route('platform.register'))
            ->assertStatus(200);
    }

    /** @test */
    public function registration_page_is_inaccessible_to_verified_users()
    {
        $this->signIn();

        $this->get(route('platform.register'))
            ->assertRedirect();
    }

    /** @test */
    public function user_can_provisionally_register_an_email_address()
    {
        Notification::fake();

        $this->signIn(create(User::class, ['email' => null]));

        $user = make(User::class);

        $this->get(route('platform.register'));
        $this->post(route('platform.register'), ['email' => $user->getEmail()])
            ->assertRedirect();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(),
            ['email' => $user->getEmail(), 'email_verified' => 0]);
        Notification::assertSentTo($this->user, VerifyEmailNotification::class);
    }

    /** @test */
    public function user_cannot_register_an_existing_email()
    {
        $this->withExceptionHandling();

        $this->signIn(create(User::class, ['email' => null]));

        $email = create(User::class)->getEmail();

        $this->get(route('platform.register'));
        $this->post(route('platform.register', ['email' => $email]))
            ->assertRedirect(route('platform.register'))
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_verify_an_email_address()
    {
        $user = create(User::class, ['emailVerified' => 0]);
        $this->signIn($user);

        $token = Str::random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user' => $user,
        ]);

        EntityManager::refresh($user);

        $response = $this->get(route('platform.register.verify', $token));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(),
            ['id' => $user->getId(), 'email_verified' => 1]);
    }

    /** @test */
    public function user_is_forced_to_configure_an_email_address()
    {
        $this->signIn(create(User::class, ['email' => null]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.register'))
            ->assertSessionMissing('error');
    }

    /** @test */
    public function user_is_forced_to_verify_an_email_address()
    {
        $this->signIn(create(User::class, ['emailVerified' => 0]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.register'))
            ->assertSessionHas('error');
    }

    /** @test */
    public function user_cannot_verify_email_address_without_a_valid_token()
    {
        $user = create(User::class, ['emailVerified' => 0]);
        $this->signIn($user);

        $token = Str::random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user_id' => $user->getId(),
        ]);

        $response = $this->get(route('platform.register.verify', Str::random(39)));
        $response->assertRedirect(route('platform.register'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(User::class)->getTableName(),
            ['id' => $user->getId(), 'email_verified' => 0]);
    }

    /** @test */
    public function user_cannot_attempt_verification_when_already_verified()
    {
        $user = create(User::class, ['emailVerified' => 1]);
        $this->signIn($user);

        $response = $this->get(route('platform.register.verify', Str::random(40)));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('error');
    }
}
