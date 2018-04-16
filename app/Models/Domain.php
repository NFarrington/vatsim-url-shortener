<?php

namespace App\Models;

/**
 * App\Models\Domain
 *
 * @property int $id
 * @property string $url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Revision[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Url[] $urls
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Domain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Domain whereUrl($value)
 * @mixin \Eloquent
 */
class Domain extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The URLs associated with this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function urls()
    {
        return $this->hasMany(Url::class);
    }

    /**
     * Set the URL with a slash appended.
     *
     * @param  string  $value
     * @return void
     */
    public function setUrlAttribute($value)
    {
        $this->attributes['url'] = ends_with($value, '/')
            ? $value
            : "{$value}/";
    }
}
