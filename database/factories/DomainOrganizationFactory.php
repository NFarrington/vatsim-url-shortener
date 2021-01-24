<?php

namespace Database\Factories;

/** @var \LaravelDoctrine\ORM\Testing\Factory $factory */

use App\Entities\Domain;
use App\Entities\DomainOrganization;
use App\Entities\Organization;
use Faker\Generator as Faker;

$factory->define(DomainOrganization::class, function (Faker $faker) {
    return [
        'domain' => function () {
            return create(Domain::class);
        },
        'organization' => function () {
            return create(Organization::class);
        },
    ];
});
