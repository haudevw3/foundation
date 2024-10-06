<?php

namespace Foundation\Routing;

class RouteGroup
{   
    /**
     * Merge route groups into a new array.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    public static function merge($new, $old)
    {
        return [
            'prefix' => static::formatPrefix($new, $old),
            'middleware' => static::formatMiddleware($new, $old),
        ];
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string
     */
    protected static function formatPrefix($new, $old)
    {
        $old = isset($old['prefix']) ? trim($old['prefix'], '/') : '';
        $new = isset($new['prefix']) ? trim($new['prefix'], '/') : '';

        return trim($old.'/'.$new);
    }

    /**
     * Format the middleware for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    protected static function formatMiddleware($new, $old)
    {
        $old = isset($old['middleware']) ? $old['middleware'] : ['web'];
        $new = isset($new['middleware']) ? $new['middleware'] : ['web'];

        return array_merge($old, $new);
    }
}