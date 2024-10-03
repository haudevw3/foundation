<?php

namespace Foundation\Container;

use Foundation\Container\Exceptions\BindingResolutionException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class Reflector
{
    /**
     * Create a new reflector instance.
     *
     * @param  string  $concrete
     * @return \ReflectionClass
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    public static function newReflectionClass($concrete)
    {
        try {
            return new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new BindingResolutionException("Target class [$concrete] does not exist.", 0, $e);
        }
    }

    /**
     * Get the class name of the given parameter type.
     *
     * @param  \ReflectionParameter  $parameter
     * @return string|null
     * 
     * @throws \Foundation\Container\Contracts\EntryNotFoundException
     */
    public static function getParameterClassName($parameter)
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (! is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }
}