<?php

namespace App\Policies;

use App\Models\Url;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UrlPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Url $url)
    {
        if ($url->organization) {
            return $user->can('create-url', $url->domain)
                && $user->can('act-as-member', $url->organization);
        }

        return $user->can('create-url', $url->domain);
    }

    /**
     * Determine whether the user can update the url.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Url $url
     * @return mixed
     */
    public function update(User $user, Url $url)
    {
        if ($url->organization) {
            return $user->can('act-as-member', $url->organization);
        }

        return $user->id == $url->user_id;
    }

    /**
     * Determine whether the user can move the url.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Url $url
     * @return mixed
     */
    public function move(User $user, Url $url)
    {
        if (!$url->domain->public) {
            return false;
        }

        if ($url->prefix) {
            return false;
        }

        if ($url->organization) {
            return $user->can('act-as-manager', $url->organization);
        }

        return $user->id == $url->user_id;
    }

    /**
     * Determine whether the user can delete the url.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Url $url
     * @return mixed
     */
    public function delete(User $user, Url $url)
    {
        if ($url->organization) {
            return $user->can('act-as-manager', $url->organization);
        }

        return $user->id == $url->user_id;
    }
}
