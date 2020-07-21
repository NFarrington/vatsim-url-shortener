<?php

namespace Tests\Unit\Services;

use App\Exceptions\Cert\InvalidResponseException;
use App\Services\VatsimService;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Services\VatsimService
 */
class VatsimServiceTest extends TestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    const USER_CID = 1104930, USER_FIRST_NAME = 'Robert', USER_LAST_NAME = 'Baratheon';

    /** @test */
    function retrieves_user_data_from_cert()
    {
        $this->given_cert_responds_with_a_valid_user();

        $user = app(VatsimService::class)->getUser(self::USER_CID);

        $this->assertArraySubset([
            'id' => self::USER_CID,
            'name_first' => self::USER_FIRST_NAME,
            'name_last' => self::USER_LAST_NAME,
        ], $user);
    }

    /** @test */
    function throws_exception_if_id_not_in_response()
    {
        $this->given_cert_responds_with_a_deleted_user();

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage(sprintf("User ID  does not match expected %s", self::USER_CID));

        app(VatsimService::class)->getUser(self::USER_CID);
    }

    /** @test */
    function throws_exception_if_missing_data()
    {
        $this->given_cert_responds_with_a_user_with_no_last_name();

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage(sprintf("Missing keys from https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=%s: name_last", self::USER_CID));

        app(VatsimService::class)->getUser(self::USER_CID);
    }

    private function given_cert_responds_with_a_valid_user()
    {
        $rawXmlResponse = /** @lang text */
            <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <root><user cid="%s"><name_last>%s</name_last><name_first>%s</name_first><email>[hidden]@example.com</email><rating>Observer</rating><regdate>2000-01-01 00:00:00</regdate><pilotrating>P1</pilotrating><country>GB</country><region>Europe</region><division>United Kingdom</division><atctime>1.111</atctime><pilottime>1.111</pilottime></user></root>
            EOT;
        $rawXmlResponse = sprintf($rawXmlResponse, self::USER_CID, self::USER_LAST_NAME, self::USER_FIRST_NAME);
        $this->mock_cert_response($rawXmlResponse);
    }

    private function given_cert_responds_with_a_deleted_user()
    {
        $rawXmlResponse = /** @lang text */
            <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <root><user cid=""><name_last></name_last><name_first></name_first><email>[hidden]</email><rating>Suspended</rating><regdate></regdate><pilotrating>P0</pilotrating><country></country><region></region><division></division><atctime></atctime><pilottime></pilottime></user></root>
            EOT;
        $this->mock_cert_response($rawXmlResponse);
    }

    private function given_cert_responds_with_a_user_with_no_last_name()
    {
        $rawXmlResponse = /** @lang text */
            <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <root><user cid="%s"><name_first>%s</name_first><email>[hidden]@example.com</email><rating>Observer</rating><regdate>2000-01-01 00:00:00</regdate><pilotrating>P1</pilotrating><country>GB</country><region>Europe</region><division>United Kingdom</division><atctime>1.111</atctime><pilottime>1.111</pilottime></user></root>
            EOT;
        $rawXmlResponse = sprintf($rawXmlResponse, self::USER_CID, self::USER_FIRST_NAME);
        $this->mock_cert_response($rawXmlResponse);
    }

    private function mock_cert_response($response)
    {
        $mock = new MockHandler([
            new Response(200, [], $response),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->instance('guzzle', $client);
    }
}
