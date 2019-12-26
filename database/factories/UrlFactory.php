<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Url;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Url::class, function (Faker $faker) {
    return [
        'domain_id' => function () {
            return ($domain = Domain::inRandomOrder()->first())
                ? $domain->id
                : create(Domain::class)->id;
        },
        'user_id' => function () {
            return create(User::class)->id;
        },
        'organization_id' => null,
        'url' => substr(implode('', $faker->unique()->words), 0, 30),
        'redirect_url' => $faker->imageUrl(),
    ];
});

$factory->state(Url::class, 'org', function (Faker $faker) {
    return [
        'organization_id' => function () {
            return create(Organization::class, ['prefix' => Str::random(3)])->id;
        },
        'user_id' => null,
    ];
});

$factory->state(Url::class, 'prefix', function (Faker $faker) {
    return [
        'prefix' => true,
    ];
});

$factory->state(Url::class, 'analytics_disabled', function (Faker $faker) {
    return [
        'analytics_disabled' => true,
    ];
});
