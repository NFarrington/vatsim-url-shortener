<?php

namespace App\Models;

/**
 * App\Models\EmailEvent
 *
 * @property int $id
 * @property string $broker
 * @property string|null $message_id
 * @property string $name
 * @property string $recipient
 * @property array $data
 * @property \Illuminate\Support\Carbon $triggered_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereBroker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailEvent whereTriggeredAt($value)
 * @mixin \Eloquent
 */
class EmailEvent extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['triggered_at'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
