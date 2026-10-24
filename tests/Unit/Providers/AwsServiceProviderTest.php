<?php

namespace Tests\Unit\Providers;

use App\Providers\AwsServiceProvider;
use Aws\CommandInterface;
use Aws\Result;
use Aws\SimpleDb\SimpleDbClient;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class AwsServiceProviderTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function simpledb_requests_have_a_content_length_matching_the_signed_body()
    {
        config()->set('services.simpledb', [
            'region' => 'us-east-1',
            'endpoint' => null,
            'key' => 'test-key',
            'secret' => 'test-secret',
            'token' => null,
        ]);
        // CreatesApplication binds a mock, so register the real client.
        $this->app->forgetInstance(SimpleDbClient::class);
        (new AwsServiceProvider($this->app))->register();
        $client = $this->app->make(SimpleDbClient::class);

        $sentRequest = null;
        $client->getHandlerList()->setHandler(function (CommandInterface $command, RequestInterface $request) use (&$sentRequest) {
            $sentRequest = $request;

            return Create::promiseFor(new Result());
        });

        $client->putAttributes([
            'DomainName' => 'TestDomain',
            'ItemName' => 'https://vats.im/test',
            'Attributes' => [
                ['Name' => 'RedirectUrl', 'Value' => 'https://example.com/', 'Replace' => true],
            ],
        ]);

        $body = (string) $sentRequest->getBody();
        $this->assertStringContainsString('Signature=', $body);
        $this->assertSame((string) strlen($body), $sentRequest->getHeaderLine('Content-Length'));
    }
}
