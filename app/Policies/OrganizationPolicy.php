<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can act as an owner of the organization.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Organization $organization
     * @return mixed
     */
    public function actAsOwner(User $user, Organization $organization)
    {
        $user = $organization->users->where('id', $user->id)->first();

        return $user && $user->pivot->role_id == OrganizationUser::ROLE_OWNER;
    }

    /**
     * Determine whether the user can act as a manager of the organization.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Organization $organization
     * @return mixed
     */
    public function actAsManager(User $user, Organization $organization)
    {
        $user = $organization->users->where('id', $user->id)->first();

        return $user && array_search(
                $user->pivot->role_id,
                [OrganizationUser::ROLE_OWNER, OrganizationUser::ROLE_MANAGER]
            ) !== false;
    }

    /**
     * Determine whether the user can act as a member of the organization.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Organization $organization
     * @return mixed
     */
    public function actAsMember(User $user, Organization $organization)
    {
        return $organization->users->where('id', $user->id)->isNotEmpty();
    }
}
