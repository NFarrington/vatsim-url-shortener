<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Organization;
use Faker\Generator as Faker;

$factory->define(Organization::class, function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->unique()->words),
    ];
});

$factory->state(Organization::class, 'prefix', function (Faker $faker) {
    return [
        'prefix' => str_random(5),
    ];
});
