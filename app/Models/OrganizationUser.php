<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    /**
     * The owner role ID.
     *
     * @var int
     */
    const ROLE_OWNER = 1;

    /**
     * The manager role ID.
     *
     * @var int
     */
    const ROLE_MANAGER = 2;

    /**
     * The member role ID.
     *
     * @var int
     */
    const ROLE_MEMBER = 3;
}
