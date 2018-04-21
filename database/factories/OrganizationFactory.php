<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Organization::class, function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->unique()->words),
    ];
});
