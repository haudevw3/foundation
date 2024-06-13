<?php

namespace Foundation\Support\Facades;

use RuntimeException;

abstract class Facade
{
    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance = [];

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     * @return void
     */
    protected static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of resolved instances.
     *
     * @return void
     */
    protected static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @return mixed
     */
    protected static function resolveFacadeInstance()
    {
        $abstract = static::getFacadeAccessor();

        if (isset(static::$resolvedInstance[$abstract])) {
            return static::$resolvedInstance[$abstract];
        }

        return static::$resolvedInstance[$abstract] = app($abstract);
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        $instance = static::resolveFacadeInstance();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        
        return $instance->$method(...$params);
    }
}
