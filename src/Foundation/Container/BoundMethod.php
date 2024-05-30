<?php

namespace Foundation\Container;

use Contracts\Container\EntryNotFoundException;
use ReflectionObject;

class BoundMethod
{
    /**
     * Invoke the specified class method and inject its dependencies if available.
     *
     * @param object $container
     * @param string $concrete
     * @param string $method
     * @return mixed
     */
    public static function call($container, $concrete, $method)
    {
        $instance = $container->get($concrete);

        $dependencies = static::getMethodDependencies($instance, $method);

        if (is_null($dependencies)) {
            return $instance->$method();
        }

        $arguments = [];

        foreach ($dependencies as $dependency) {
            $arguments[] = $container->build($dependency);
        }
        
        return $instance->$method(...$arguments);
    }

    /**
     * Retrieve the dependencies associated with the given instance and method name.
     *
     * @param object $instance
     * @param string $method
     * @return array|null
     * 
     * @throws \EntryNotFoundException
     */
    protected static function getMethodDependencies($instance, $method)
    {
        $reflector = new ReflectionObject($instance);

        if (! $reflector->hasMethod($method)) {
            throw new EntryNotFoundException("Target method [$method] does not exist.");
        }

        $method = $reflector->getMethod($method);

        $parameters = $method->getParameters();

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = Util::getParameterClassName($parameter);
        }

        return ! empty($dependencies) ? $dependencies : null;
    }
}