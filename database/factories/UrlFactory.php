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
        'organization_id' => null,
        'url' => substr(implode('', $faker->unique()->words), 0, 30),
        'redirect_url' => $faker->imageUrl(),
    ];
});

$factory->state(\App\Models\Url::class, 'org', function ($faker) {
    return [
        'organization_id' => function () {
            return create(\App\Models\Organization::class, ['prefix' => str_random(3)])->id;
        },
        'user_id' => null,
    ];
});
