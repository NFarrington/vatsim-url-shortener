<?php

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use App\Entities\Domain;
use App\Entities\Url;
use App\Entities\UrlAnalytics;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;

$factory->define(UrlAnalytics::class, function (Faker $faker) {
    return [
        'user' => null,
        'url' => function () {
            return create(Url::class);
        },
        'requestTime' => mt_rand(),
        'httpHost' => make(Domain::class)->url,
        'httpReferer' => null,
        'httpUserAgent' => $faker->userAgent,
        'remoteAddr' => Arr::random([$faker->ipv4, $faker->ipv6]),
        'requestUri' => make(Url::class)->url,
        'getData' => [],
        'customHeaders' => [],
        'responseCode' => 302,
    ];
});
