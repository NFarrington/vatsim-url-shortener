<?php

namespace App\Models;

/**
 * App\Models\Revision
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int|null $user_id
 * @property string $key
 * @property string|null $old_value
 * @property string|null $new_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereOldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Revision whereUserId($value)
 * @mixin \Eloquent
 */
class Revision extends Model
{
    //
}
