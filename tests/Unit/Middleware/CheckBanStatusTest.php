<?php

namespace Tests\Unit\Middleware;

use App\Entities\User;
use App\Http\Middleware\CheckBanStatus;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class CheckBanStatusTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function ignores_unbanned_users()
    {
        $user = make(User::class);
        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $middleware = new CheckBanStatus();
        $result = $middleware->handle($request, fn() => 'next');

        $this->assertEquals('next', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function redirects_banned_users()
    {
        $user = make(User::class);
        $user->setBanned(true);
        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(10)));
        $middleware = new CheckBanStatus();
        $result = $middleware->handle($request, fn() => 'next');

        $this->assertNotEquals('next', $result);
        $this->assertArrayHasKey('error', $result->getSession()->all());
    }
}
