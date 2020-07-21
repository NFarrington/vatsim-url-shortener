<?php

namespace App\Repositories;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;

class OrganizationUserRepository extends Repository
{
    public function findByUserAndOrganization(User $user, Organization $organization): ?OrganizationUser
    {
        return $this->findOneBy(['user' => $user, 'organization' => $organization]);
    }
}
