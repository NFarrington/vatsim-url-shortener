<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use Faker\Generator as Faker;

$factory->define(OrganizationUser::class, function (Faker $faker) {
    return [
        'user' => function () {
            return create(User::class);
        },
        'organization' => function () {
            return create(Organization::class);
        },
        'roleId' => OrganizationUser::ROLE_MEMBER,
    ];
});
