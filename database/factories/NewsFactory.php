<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use Faker\Generator as Faker;

$factory->define(\App\Entities\News::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
        'published' => true,
    ];
});
