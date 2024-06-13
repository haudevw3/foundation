<?php

use Foundation\Support\TapChainingProxy;

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new TapChainingProxy($value);
        }

        $callback($value);

        return $value;
    }
}