<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
    ];
});
