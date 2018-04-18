<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SystemUser::class, function (Faker $faker) {
    return [
        'username' => $faker->userName,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
    ];
});
