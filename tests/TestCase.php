<?php

namespace Tests;

use App\Exceptions\Handler;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * The user currently signed in.
     *
     * @var \App\Models\User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    protected function signIn($user = null)
    {
        $this->user = $user ?: create(User::class);

        $this->actingAs($this->user);

        return $this;
    }

    protected function signInAdmin($user = null)
    {
        $this->signIn($user);

        config(['auth.admins' => [$this->user->id]]);

        return $this;
    }
}
