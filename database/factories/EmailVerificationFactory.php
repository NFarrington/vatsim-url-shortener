<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\EmailVerification;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(EmailVerification::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return create(User::class)->id;
        },
        'token' => Hash::make(str_random(40)),
    ];
});
