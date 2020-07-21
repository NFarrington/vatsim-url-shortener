<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\StartOptionalSession;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Tests\TestCase;

class StartOptionalSessionTest extends TestCase
{
    /** @test */
    public function doesnt_throw_an_exception_when_the_session_cannot_be_started()
    {
        $request = new Request();
        $middleware = new StartOptionalSession(new class(app()) extends SessionManager {
            protected function buildSession($handler)
            {
                throw new Exception();
            }
        });

        try {
            $middleware->handle($request, fn() => null);
        } catch (Exception $e) {
            $this->fail("Middleware threw an exception.");
        }
        $this->expectNotToPerformAssertions();
    }
}
