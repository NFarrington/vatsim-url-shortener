<?php

namespace App\Providers;

use GuzzleHttp\Client as GuzzleClient;
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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::macro('diffForHumansAt', function () {
            $diffDays = $this->diffInDays(self::now()->endOfDay());
            switch ($diffDays) {
                case 0:
                    $time = 'Today';
                    break;
                case 1:
                    $time = 'Yesterday';
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $time = "{$diffDays} days ago";
                    break;
                default:
                    $time = $this->format('Y-m-d');
            }

            $time .= ' at '.$this->format('H:i');

            return $time;
        });
    }
}
