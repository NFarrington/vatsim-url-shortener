<?php

namespace App\Models;

/**
 * App\Models\EmailVerification
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Carbon\Carbon|null $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailVerification whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailVerification whereUserId($value)
 * @mixin \Eloquent
 */
class EmailVerification extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The user whose email is being verified.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
