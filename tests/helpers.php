<?php

/**
 * @param $class
 * @param array $attributes
 * @param null $times
 * @return \Illuminate\Support\Collection
 */
function create($class, $attributes = [], $times = null)
{
    return entity($class, $times)->create($attributes);
}

/**
 * @param $class
 * @param array $attributes
 * @param null $times
 * @return \Illuminate\Support\Collection
 */
function make($class, $attributes = [], $times = null)
{
    return entity($class, $times)->make($attributes);
}
