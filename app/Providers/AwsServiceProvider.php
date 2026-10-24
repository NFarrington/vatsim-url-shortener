<?php

namespace App\Providers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Middleware;
use Aws\SimpleDb\SimpleDbClient;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestInterface;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SimpleDbClient::class, function ($app) {
            $config = config('services.simpledb');

            $simpleDbConfig = [
                'region' => $config['region'],
                'version' => '2009-04-15',
                'endpoint' => $config['endpoint'] ?? null,
            ];

            if ($config['key'] && $config['secret']) {
                $simpleDbConfig['credentials'] = Arr::only(
                    $config, ['key', 'secret', 'token']
                );
            }

            $client = new SimpleDbClient($simpleDbConfig);

            // The bridge's SignatureV2 signer replaces the body with one that includes the signature, but keeps the
            // serializer's Content-Length. Guzzle 8 rejects requests whose Content-Length doesn't match the body.
            $client->getHandlerList()->appendSign(
                Middleware::mapRequest(function (RequestInterface $request) {
                    return $request->withHeader('Content-Length', (string) $request->getBody()->getSize());
                }),
                'content-length-after-signing'
            );

            return $client;
        });

        $this->app->singleton(DynamoDbClient::class, function ($app) {
            $config = config('services.dynamodb');

            $dynamoDbConfig = [
                'region' => $config['region'],
                'version' => '2012-08-10',
                'endpoint' => $config['endpoint'] ?? null,
            ];

            if ($config['key'] && $config['secret']) {
                $dynamoDbConfig['credentials'] = Arr::only(
                    $config, ['key', 'secret', 'token']
                );
            }

            return new DynamoDbClient($dynamoDbConfig);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
