<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Domain::class, function (Faker $faker) {
    return [
        'url' => 'https://'.$faker->domainName.'/',
    ];
});
