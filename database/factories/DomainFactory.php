<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Domain;
use Faker\Generator as Faker;

$factory->define(Domain::class, function (Faker $faker) {
    return [
        'url' => 'https://'.$faker->domainName.'/',
        'public' => 1,
    ];
});

$factory->state(Domain::class, 'private', function (Faker $faker) {
    return [
        'public' => 0,
    ];
});
