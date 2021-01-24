<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use App\Entities\EmailVerification;
use App\Entities\User;
use Faker\Generator as Faker;
use Hash;
use Illuminate\Support\Str;

$factory->define(EmailVerification::class, function (Faker $faker) {
    return [
        'user' => function () {
            return create(User::class);
        },
        'token' => Hash::make(Str::random(40)),
    ];
});
