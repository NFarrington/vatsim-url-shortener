<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use Faker\Generator as Faker;

$factory->define(\App\Entities\SystemUser::class, function (Faker $faker) {
    return [
        'username' => $faker->userName,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
    ];
});
