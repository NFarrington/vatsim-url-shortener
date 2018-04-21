<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    /**
     * The manager role ID.
     *
     * @var int
     */
    const ROLE_MANAGER = 1;

    /**
     * The member role ID.
     *
     * @var int
     */
    const ROLE_MEMBER = 2;
}
