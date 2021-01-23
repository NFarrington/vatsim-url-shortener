<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */

use App\Entities\Domain;
use App\Entities\Organization;
use App\Entities\Url;
use App\Entities\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Facades\EntityManager;

$factory->define(Url::class, function (Faker $faker) {
    return [
        'domain' => function () {
            $domains = EntityManager::getRepository(Domain::class)->findAll();
            $domain = count($domains) > 0 ? $domains[0] : null;
            return $domain ?: create(Domain::class);
        },
        'user' => function () {
            return create(User::class);
        },
        'organization' => null,
        'url' => substr(implode('', $faker->unique()->words), 0, 30),
        'redirectUrl' => $faker->imageUrl(),
    ];
});

$factory->state(Url::class, 'org', function (Faker $faker) {
    return [
        'organization' => function () {
            return create(Organization::class, ['prefix' => Str::random(3)]);
        },
        'user' => null,
    ];
});

$factory->state(Url::class, 'prefix', function (Faker $faker) {
    return [
        'prefix' => true,
    ];
});

$factory->state(Url::class, 'analytics_disabled', function (Faker $faker) {
    return [
        'analyticsDisabled' => true,
    ];
});
