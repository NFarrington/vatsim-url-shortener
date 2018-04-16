<?php

namespace Tests\Feature\Platform;

use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Vatsim\OAuth\SSO;
use Vatsim\OAuth\SSOException;

class VatsimLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_can_initiate_vatsim_login()
    {
        $token = json_decode('{"request":{"result":"success","message":""},"token":{"oauth_token":"SSO_DEMO_'.str_random().'","oauth_token_secret":"'.str_random().'","oauth_callback_confirmed":true}}');
        $mock = $this->createMock(SSO::class);
        $mock->method('requestToken')->willReturn($token);
        $this->app->instance('vatsimoauth', $mock);

        $this->post(route('login.vatsim'))
            ->assertRedirect();
    }

    /** @test */
    function user_can_complete_vatsim_login()
    {
        $ssoRequest = json_decode('{"request":{"result":"success","message":""},"user":{"id":"1300001","name_first":"1st","name_last":"Test","rating":{"id":"1","short":"OBS","long":"Pilot\/Observer","GRP":"Pilot\/Observer"},"pilot_rating":{"rating":"0"},"experience":"N","reg_date":"2014-05-14 17:17:26","country":{"code":"GB","name":"United Kingdom"},"region":{"code":"EUR","name":"Europe"},"division":{"code":"GBR","name":"United Kingdom"},"subdivision":{"code":null,"name":null}}}');
        $mock = $this->createMock(SSO::class);
        $mock->method('checkLogin')->willReturn($ssoRequest);
        $this->app->instance('vatsimoauth', $mock);

        $this->get(route('login.vatsim.callback'))
            ->assertRedirect();
        $this->assertTrue(Auth::check());
    }

    /** @test */
    function failed_login_redirects_with_error()
    {
        $mock = $this->createMock(SSO::class);
        $mock->method('checkLogin')->willThrowException(new SSOException('checkLogin failed'));
        $this->app->instance('vatsimoauth', $mock);

        $this->get(route('login.vatsim.callback'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** @test */
    function vatsim_login_is_ratelimited()
    {
        $ssoRequest = json_decode('{"request":{"result":"success","message":""},"user":{"id":"1300001","name_first":"1st","name_last":"Test","rating":{"id":"1","short":"OBS","long":"Pilot\/Observer","GRP":"Pilot\/Observer"},"pilot_rating":{"rating":"0"},"experience":"N","reg_date":"2014-05-14 17:17:26","country":{"code":"GB","name":"United Kingdom"},"region":{"code":"EUR","name":"Europe"},"division":{"code":"GBR","name":"United Kingdom"},"subdivision":{"code":null,"name":null}}}');
        $mock = $this->createMock(SSO::class);
        $mock->method('checkLogin')->willReturn($ssoRequest);
        $this->app->instance('vatsimoauth', $mock);

        $mock = $this->createMock(RateLimiter::class);
        $mock->method('tooManyAttempts')->willReturn(true);
        $this->app->instance(RateLimiter::class, $mock);

        $this->get(route('login.vatsim.callback'))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }
}
