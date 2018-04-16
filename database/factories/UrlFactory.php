<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Url::class, function (Faker $faker) {
    return [
        'domain_id' => function () {
            return ($domain = \App\Models\Domain::inRandomOrder()->first())
                ? $domain->id
                : create(\App\Models\Domain::class)->id;
        },
        'user_id' => function () {
            return create(\App\Models\User::class)->id;
        },
        'url' => implode('', $faker->unique()->words),
        'redirect_url' => $faker->imageUrl(),
    ];
});
