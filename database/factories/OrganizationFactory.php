<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */

use App\Entities\Organization;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Organization::class, function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->unique()->words),
    ];
});

$factory->state(Organization::class, 'prefix', function (Faker $faker) {
    return [
        'prefix' => Str::random(5),
    ];
});
