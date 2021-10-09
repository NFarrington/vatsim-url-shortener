<?php

namespace Tests\Unit\Services;

use App\Exceptions\Cert\InvalidResponseException;
use App\Services\VatsimService;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

/**
 * @covers \App\Services\VatsimService
 */
class VatsimServiceTest extends TestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    const USER_CID = 1104930;

    /** @test */
    function retrieves_user_data_from_cert()
    {
        $this->given_cert_responds_with_a_valid_user();

        $user = app(VatsimService::class)->getUser(self::USER_CID);

        $this->assertArraySubset([
            'id' => self::USER_CID,
        ], $user);
    }

    /** @test */
    function throws_exception_if_id_not_in_response()
    {
        $this->given_cert_responds_with_a_missing_user();

        $this->assertThrowsWithMessage(
            InvalidResponseException::class,
            sprintf("missing keys from https://api.vatsim.net/api/ratings/%s/: id", self::USER_CID),
            fn() => app(VatsimService::class)->getUser(self::USER_CID)
        );
    }

    /** @test */
    function throws_exception_if_id_is_different_from_expected()
    {
        $this->given_cert_responds_with_a_valid_user(1234);

        $this->assertThrowsWithMessage(
            InvalidResponseException::class,
            sprintf("user id 1234 does not match expected %s", self::USER_CID),
            fn() => app(VatsimService::class)->getUser(self::USER_CID)
        );
    }

    private function given_cert_responds_with_a_valid_user($id = self::USER_CID)
    {
        $rawXmlResponse = /** @lang text */
            <<<EOT
            {"id":"%s","rating":4,"pilotrating":0,"susp_date":null,"reg_date":"2009-04-08T21:51:39","region":"EMEA","division":"GBR","subdivision":" ","lastratingchange":"2014-09-27T11:49:39"}
            EOT;
        $rawXmlResponse = sprintf($rawXmlResponse, $id);
        $this->mock_cert_response($rawXmlResponse);
    }

    private function given_cert_responds_with_a_missing_user()
    {
        $rawXmlResponse = /** @lang text */
            <<<EOT
            {"detail":"Not found."}
            EOT;
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
