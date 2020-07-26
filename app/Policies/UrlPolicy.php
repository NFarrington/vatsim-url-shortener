<?php

namespace App\Policies;

use App\Entities\Url;
use App\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class UrlPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Url $url)
    {
        if ($url->getOrganization()) {
            return Gate::check('create-url', $url->getDomain())
                && Gate::check('act-as-member', $url->getOrganization());
        }

        return Gate::check('create-url', $url->getDomain());
    }

    public function update(User $user, Url $url)
    {
        if ($url->getOrganization()) {
            $organization = $url->getOrganization();
            $organization->getId();

            return Gate::check('act-as-member', $organization);
        }

        return $user == $url->getUser();
    }

    public function move(User $user, Url $url)
    {
        if (!$url->getDomain()->isPublic()) {
            return false;
        }

        if ($url->isPrefixed()) {
            return false;
        }

        if ($url->getOrganization()) {
            return Gate::check('act-as-manager', $url->getOrganization());
        }

        return $user == $url->getUser();
    }

    public function delete(User $user, Url $url)
    {
        if ($url->getOrganization()) {
            return Gate::check('act-as-manager', $url->getOrganization());
        }

        return $user == $url->getUser();
    }
}
