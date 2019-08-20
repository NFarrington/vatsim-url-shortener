<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\OrganizationPrefixApplication
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property string $identity_url
 * @property string $prefix
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @property-read \App\Models\Organization $organization
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrganizationPrefixApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereIdentityUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrganizationPrefixApplication whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrganizationPrefixApplication withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrganizationPrefixApplication withoutTrashed()
 * @mixin \Eloquent
 */
class OrganizationPrefixApplication extends Model
{
    use SoftDeletes;

    /**
     * The application's organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
