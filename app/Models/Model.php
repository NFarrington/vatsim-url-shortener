<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * App\Models\Model
 *
 * @mixin \Eloquent
 */
abstract class Model extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
