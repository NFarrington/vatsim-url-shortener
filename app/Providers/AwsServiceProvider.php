<?php

namespace App\Providers;

use Aws\SimpleDb\SimpleDbClient;
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

            return new SimpleDbClient($simpleDbConfig);
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
