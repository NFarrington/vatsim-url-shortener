<?php

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use App\Entities\Domain;
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
