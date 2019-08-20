<?php

namespace Tests\Unit;

use App\Exceptions\Cert\InvalidResponseException;
use App\Libraries\Vatsim;
use App\Models\User;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VatsimTest extends TestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    /** @test */
    function can_retrieve_user_data_from_cert()
    {
        $template = make(User::class);
        $response = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<root><user cid="{$template->id}"><name_last>{$template->last_name}</name_last><name_first>{$template->first_name}</name_first><email>[hidden]@example.com</email><rating>Observer</rating><regdate>2000-01-01 00:00:00</regdate><pilotrating>P1</pilotrating><country>GB</country><region>Europe</region><division>United Kingdom</division><atctime>1.111</atctime><pilottime>1.111</pilottime></user></root>
EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);

        $user = (new Vatsim)->getUser($template->id);
        $this->assertArraySubset([
            'id' => $template->id,
            'name_first' => $template->first_name,
            'name_last' => $template->last_name,
        ], $user);
    }

    /** @test */
    function throws_exception_if_id_not_in_response()
    {
        $template = make(User::class);
        $response = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<root><user cid=""><name_last></name_last><name_first></name_first><email>[hidden]</email><rating>Suspended</rating><regdate></regdate><pilotrating>P0</pilotrating><country></country><region></region><division></division><atctime></atctime><pilottime></pilottime></user></root>
EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);

        $this->expectException(InvalidResponseException::class);
        (new Vatsim)->getUser($template->id);
    }

    /** @test */
    function throws_exception_if_missing_data()
    {
        $template = make(User::class);
        $response = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<root><user cid="{$template->id}"><name_first>{$template->first_name}</name_first><email>[hidden]@example.com</email><rating>Observer</rating><regdate>2000-01-01 00:00:00</regdate><pilotrating>P1</pilotrating><country>GB</country><region>Europe</region><division>United Kingdom</division><atctime>1.111</atctime><pilottime>1.111</pilottime></user></root>
EOT;
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);

        $this->expectException(InvalidResponseException::class);
        (new Vatsim)->getUser($template->id);
    }
}
