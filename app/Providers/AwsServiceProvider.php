<?php

namespace App\Providers;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
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
