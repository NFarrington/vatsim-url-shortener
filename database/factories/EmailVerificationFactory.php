<?php

use App\Models\EmailVerification;
use Faker\Generator as Faker;

$factory->define(EmailVerification::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return create(\App\Models\User::class)->id;
        },
        'token' => Hash::make(str_random(40)),
    ];
});
