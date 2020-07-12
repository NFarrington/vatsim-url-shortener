<?php

namespace App\Policies;

use App\Entities\Domain;
use App\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DomainPolicy
{
    use HandlesAuthorization;

    public function createUrl(User $user, Domain $domain)
    {
        if ($domain->isPublic()) {
            return true;
        }

        foreach ($user->getOrganizations() as $organization) {
            foreach ($organization->getDomains() as $ownedDomain) {
                if ($domain->getId() === $ownedDomain->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
