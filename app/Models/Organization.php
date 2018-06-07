<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property string $prefix
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $managers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $owners
 * @property-read \App\Models\OrganizationPrefixApplication $prefixApplication
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Url[] $urls
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organization onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organization withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organization withoutTrashed()
 * @mixin \Eloquent
 */
class Organization extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are trackable.
     *
     * @var array
     */
    protected $tracked = ['name'];

    /**
     * The organization's URLs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function urls()
    {
        return $this->hasMany(Url::class);
    }

    /**
     * The organization's owners.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id')
            ->withTimestamps()
            ->using(OrganizationUser::class)
            ->wherePivot('role_id', OrganizationUser::ROLE_OWNER)
            ->whereNull('organization_user.deleted_at');
    }

    /**
     * The organization's managers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managers()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id')
            ->withTimestamps()
            ->using(OrganizationUser::class)
            ->wherePivot('role_id', OrganizationUser::ROLE_MANAGER)
            ->whereNull('organization_user.deleted_at');
    }

    /**
     * The organization's members.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id')
            ->withTimestamps()
            ->using(OrganizationUser::class)
            ->wherePivot('role_id', OrganizationUser::ROLE_MEMBER)
            ->whereNull('organization_user.deleted_at');
    }

    /**
     * The organization's users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_id')
            ->withTimestamps()
            ->using(OrganizationUser::class)
            ->whereNull('organization_user.deleted_at');
    }

    /**
     * The organization's prefix application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function prefixApplication()
    {
        return $this->hasOne(OrganizationPrefixApplication::class);
    }
}
