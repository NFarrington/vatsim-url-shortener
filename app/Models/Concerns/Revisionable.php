<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

trait Revisionable
{
    /**
     * The model's original attributes.
     *
     * @var array
     */
    private $originalTracked = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootRevisionable()
    {
        static::registerModelEvent('saving', function ($model) {
            $model->rememberOriginal();
        });

        static::registerModelEvent('saved', function ($model) {
            $model->trackChanges();
        });
    }

    /**
     * Store the original values of the attributes.
     */
    private function rememberOriginal()
    {
        $this->originalTracked = array_intersect_key($this->getOriginal(), $this->getDirty());
    }

    /**
     * Store the changes in the database.
     */
    private function trackChanges()
    {
        $attributes = $this->originalTracked;

        foreach ($this->trackableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            if ($this->isTrackable($key) && $value != $this->$key) {
                $dataChange = new \App\Models\Revision();
                $dataChange->user_id = Auth::check() ? Auth::user()->id : null;
                $dataChange->key = $key;
                $dataChange->old_value = $value;
                $dataChange->new_value = is_array($this->$key) ? json_encode($this->$key) : $this->$key;
                $this->dataChanges()->save($dataChange);
            }
        }

        $this->originalTracked = [];
    }

    /**
     * Relationship for the model's data changes.
     *
     * @return mixed
     */
    private function dataChanges()
    {
        return $this->morphMany(\App\Models\Revision::class, 'model')
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Get the tracked attributes of the model.
     *
     * @return array
     */
    private function getTracked()
    {
        return isset($this->tracked) ? $this->tracked : [];
    }

    /**
     * Determine if the given attribute is tracked.
     *
     * @param  string $key
     * @return bool
     */
    private function isTrackable($key)
    {
        return in_array($key, $this->getTracked());
    }

    /**
     * Get the trackable attributes of a given array.
     *
     * @param  array $attributes
     * @return array
     */
    private function trackableFromArray(array $attributes)
    {
        return array_intersect_key($attributes, array_flip($this->getTracked()));
    }
}
