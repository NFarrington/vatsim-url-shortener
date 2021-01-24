<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use App\Entities\Organization;
use App\Entities\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\App\Entities\OrganizationPrefixApplication::class, function (Faker $faker) {
    return [
        'organization' => function () {
            return create(Organization::class);
        },
        'user' => function () {
            return create(User::class);
        },
        'identityUrl' => $faker->url,
        'prefix' => Str::random(3),
    ];
});
