<?php

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use Faker\Generator as Faker;

$factory->define(App\Entities\User::class, function (Faker $faker) {
    return [
        'id' => mt_rand(800000, 2000000),
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'email' => $faker->email,
        'emailVerified' => 1,
    ];
});
