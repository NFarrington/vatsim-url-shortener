<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\OrganizationUser
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationUser whereUserId($value)
 * @mixin \Eloquent
 */
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
