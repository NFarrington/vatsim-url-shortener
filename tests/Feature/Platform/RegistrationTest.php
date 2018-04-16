<?php

namespace Tests\Feature\Platform;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function registration_page_loads_successfully()
    {
        $this->signIn(create(User::class, ['email' => null]));

        $this->get(route('register'))
            ->assertStatus(200);
    }

    /** @test */
    function registration_page_is_inaccessible_to_verified_users()
    {
        $this->signIn();

        $this->get(route('register'))
            ->assertRedirect();
    }

    /** @test */
    function user_can_provisionally_register_an_email_address()
    {
        $this->signIn(create(User::class, ['email' => null]));

        $user = make(User::class);

        $this->get(route('register'));
        $this->post(route('register'), ['email' => $user->email])
            ->assertRedirect();
        $this->assertDatabaseHas($user->getTable(), ['email' => $user->email, 'email_verified' => 0]);
    }

    /** @test */
    function user_cannot_register_an_existing_email()
    {
        $this->expectException(ValidationException::class);

        $this->signIn(create(User::class, ['email' => null]));

        $email = create(User::class)->email;

        $this->get(route('register'));
        $this->post(route('register', ['email' => $email]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    /** @test */
    function user_can_verify_an_email_address()
    {
        $user = create(User::class, ['email_verified' => 0]);
        $this->signIn($user);

        $token = str_random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('register.verify', $token));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id, 'email_verified' => 1]);
    }

    /** @test */
    function user_is_forced_to_configure_an_email_address()
    {
        $this->signIn(create(User::class, ['email' => null]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('register'))
            ->assertSessionMissing('error');
    }

    /** @test */
    function user_is_forced_to_verify_an_email_address()
    {
        $this->signIn(create(User::class, ['email_verified' => 0]));

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('error');
    }

    /** @test */
    function user_cannot_verify_email_address_without_a_valid_token()
    {
        $user = create(User::class, ['email_verified' => 0]);
        $this->signIn($user);

        $token = str_random(40);
        create(EmailVerification::class, [
            'token' => Hash::make($token),
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('register.verify', str_random(39)));
        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id, 'email_verified' => 0]);
    }

    /** @test */
    function user_cannot_attempt_verification_when_already_verified()
    {
        $user = create(User::class, ['email_verified' => 1]);
        $this->signIn($user);

        $response = $this->get(route('register.verify', str_random(40)));
        $response->assertRedirect(route('platform.dashboard'));
        $response->assertSessionHas('error');
    }
}
