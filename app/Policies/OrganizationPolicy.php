<?php

namespace App\Policies;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    public function actAsOwner(User $user, Organization $organization)
    {
        $organizationOwners = $organization->getUsers(OrganizationUser::ROLE_OWNER);
        foreach ($organizationOwners as $owner) {
            if ($owner->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function actAsManager(User $user, Organization $organization)
    {
        $organizationManagers = $organization->getUsers(OrganizationUser::ROLE_MANAGER);
        foreach ($organizationManagers as $manager) {
            if ($manager->getId() === $user->getId()) {
                return true;
            }
        }

        return $this->actAsOwner($user, $organization);
    }

    public function actAsMember(User $user, Organization $organization)
    {
        $organizationUsers = $organization->getUsers();
        foreach ($organizationUsers as $user) {
            if ($user->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
