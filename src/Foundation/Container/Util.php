<?php

namespace Foundation\Container;

use Contracts\Container\EntryNotFoundException;
use ReflectionNamedType;

class Util
{
    /**
     * Get the class name of the given parameter type.
     *
     * @param \ReflectionParameter $parameter
     * @return string
     * 
     * @throws \EntryNotFoundException
     */
    public static function getParameterClassName($parameter)
    {
        $type = $parameter->getType();

        $name = $type->getName();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new EntryNotFoundException("Entry not found for [$name]. Accepts only interface name or class name.");
        }

        return $name;
    }
}