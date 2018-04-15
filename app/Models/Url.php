<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Url
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $url
 * @property string $redirect_url
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @property-read \App\Models\User|null $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Url onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url public()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Url whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Url withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Url withoutTrashed()
 * @mixin \Eloquent
 */
class Url extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are trackable.
     *
     * @var array
     */
    protected $tracked = ['url', 'redirect_url'];

    /**
     * The user the URL is owned by.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only publicly viewable URLs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->whereNull('user_id');
    }
}
