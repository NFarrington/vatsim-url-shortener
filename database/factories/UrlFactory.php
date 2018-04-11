<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Url::class, function (Faker $faker) {
    return [
        'short_url' => implode('', $faker->unique()->words),
        'redirect_url' => $faker->imageUrl(),
    ];
});
