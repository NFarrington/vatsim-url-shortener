<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Url;
use Illuminate\Auth\Access\HandlesAuthorization;

class UrlPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the url.
     *
     * @param  \App\Models\User $user
     * @param \App\Models\Url $url
     * @return mixed
     */
    public function update(User $user, Url $url)
    {
        if ($url->organization) {
            return $user->can('view', $url->organization);
        }

        return $user->id == $url->user_id;
    }

    /**
     * Determine whether the user can delete the url.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Url  $url
     * @return mixed
     */
    public function delete(User $user, Url $url)
    {
        if ($url->organization) {
            return $user->can('view', $url->organization);
        }

        return $user->id == $url->user_id;
    }
}
