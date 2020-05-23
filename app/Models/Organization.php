<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property string|null $prefix
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $managers
 * @property-read int|null $managers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $owners
 * @property-read int|null $owners_count
 * @property-read \App\Models\OrganizationPrefixApplication|null $prefixApplication
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Url[] $urls
 * @property-read int|null $urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Organization onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Organization sortable($defaultParameters = null)
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
    use SoftDeletes, Sortable;

    /**
     * The attributes that are trackable.
     *
     * @var array
     */
    protected $tracked = ['name'];

    /**
     * Sortable attributes.
     *
     * @var array
     */
    public $sortable = [
        'id',
        'name',
        'created_at',
        'updated_at',
    ];

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
