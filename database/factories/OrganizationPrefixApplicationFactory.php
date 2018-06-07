<?php

use Faker\Generator as Faker;

$factory->define(App\Models\OrganizationPrefixApplication::class, function (Faker $faker) {
    return [
        'organization_id' => function () {
            return create(\App\Models\Organization::class)->id;
        },
        'user_id' => function () {
            return create(\App\Models\User::class)->id;
        },
        'identity_url' => $faker->url,
        'prefix' => str_random(3),
    ];
});
