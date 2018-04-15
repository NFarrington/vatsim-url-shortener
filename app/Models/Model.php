<?php

namespace App\Models;

use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * App\Models\Model
 *
 * @method static int count(string $columns = '*')
 * @method static $this inRandomOrder(string $seed = '')
 * @mixin \Eloquent
 */
abstract class Model extends BaseModel
{
    use Revisionable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
