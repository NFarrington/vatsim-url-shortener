<?php

namespace Tests\Feature\Platform;

use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
        $this->signIn(create(User::class, ['email' => null]));

        $this->expectsNotification(
            $this->user,
            VerifyEmailNotification::class
        );

        $user = make(User::class);

        $this->get(route('platform.register'));
        $this->post(route('platform.register'), ['email' => $user->email])
            ->assertRedirect();
        $this->assertDatabaseHas($user->getTable(), ['email' => $user->email, 'email_verified' => 0]);
    }

    /** @test */
    public function user_cannot_register_an_existing_email()
    {
        $this->expectException(ValidationException::class);

        $this->signIn(create(User::class, ['email' => null]));

        $email = create(User::class)->email;

        $this->get(route('platform.register'));
        $this->post(route('platform.register', ['email' => $email]))
            ->assertRedirect(route('platform.register'))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing($this->user->getTable(), ['email' => $email]);
    }

    /** @test */
    public function user_can_verify_an_email_address()
    {
        $user = create(User::class, ['email_verified' => 0]);
        $this->signIn($user);

        $token = str_random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('platform.register.verify', $token));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id, 'email_verified' => 1]);
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
        $this->signIn(create(User::class, ['email_verified' => 0]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.register'))
            ->assertSessionHas('error');
    }

    /** @test */
    public function user_cannot_verify_email_address_without_a_valid_token()
    {
        $user = create(User::class, ['email_verified' => 0]);
        $this->signIn($user);

        $token = str_random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('platform.register.verify', str_random(39)));
        $response->assertRedirect(route('platform.register'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id, 'email_verified' => 0]);
    }

    /** @test */
    public function user_cannot_attempt_verification_when_already_verified()
    {
        $user = create(User::class, ['email_verified' => 1]);
        $this->signIn($user);

        $response = $this->get(route('platform.register.verify', str_random(40)));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('error');
    }
}
