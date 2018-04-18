<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * App\Models\BasicUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser whereUsername($value)
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
