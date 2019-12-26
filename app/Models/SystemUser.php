<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * App\Models\SystemUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @property-read int|null $data_changes_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemUser whereUsername($value)
 * @mixin \Eloquent
 */
class SystemUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
