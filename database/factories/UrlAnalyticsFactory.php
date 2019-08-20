<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Domain;
use App\Models\Url;
use App\Models\UrlAnalytics;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;

$factory->define(UrlAnalytics::class, function (Faker $faker) {
    return [
        'user_id' => null,
        'url_id' => function () {
            return create(Url::class)->id;
        },
        'request_time' => mt_rand(),
        'http_host' => make(Domain::class)->url,
        'http_referer' => null,
        'http_user_agent' => $faker->userAgent,
        'remote_addr' => Arr::random([$faker->ipv4, $faker->ipv6]),
        'request_uri' => make(Url::class)->url,
        'get_data' => [],
        'custom_headers' => [],
        'response_code' => 302,
    ];
});
