<?php

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */
use App\Entities\EmailVerification;
use App\Entities\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(EmailVerification::class, function (Faker $faker) {
    return [
        'user' => function () {
            return create(User::class);
        },
        'token' => Hash::make(Str::random(40)),
    ];
});
