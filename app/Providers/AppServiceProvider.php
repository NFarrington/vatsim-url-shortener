<?php

namespace App\Providers;

use App\Macros\CarbonMacros;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function register()
    {
        $this->app->bind('guzzle', function () {
            return new GuzzleClient(['timeout' => 5]);
        });

        $this->app->alias('migrator', Migrator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::macro('diffForHumansAt', function () {
            return CarbonMacros::diffForHumansAt($this);
        });
    }
}
