<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Organization::class, function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->unique()->words),
    ];
});

$factory->state(\App\Models\Organization::class, 'prefix', function (Faker $faker) {
    return [
        'prefix' => str_random(5),
    ];
});
