<?php

namespace Foundation\Contracts\Container;

interface ContainerContract
{
    /**
     * Check if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound($abstract);

    /**
     * Establish alias and place them into the respective array.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     * 
     * @throws \LogicException
     */
    public function alias($abstract, $alias);

    /**
     * Register a binding with the container.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @param bool $shared
     * @return void
     * 
     * @throws \TypeError
     */
    public function bind($abstract, $concrete = null, $shared = false);

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @param bool $shared
     * @return void
     */
    public function bindIf($abstract, $concrete = null, $shared = false);

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null);

    /**
     * Register a shared binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @return void
     */
    public function singletonIf($abstract, $concrete = null);

    /**
     * The factory produces objects based on the given bindings.
     *
     * @return void
     */
    public function factory();

    /**
     * Get the instance with the given name.
     *
     * @param string $name
     * @return object
     */
    public function get($name);

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string $concrete
     * @return object
     */
    public function build($concrete);

    /**
     * Instantiate a abstract scoped instance of the given type.
     *
     * @param string $abstract
     * @return void
     * 
     * @throws \BindingResolutionException
     */
    public function make($abstract);

    /**
     * Call the given class method and inject its dependencies.
     *
     * @param string $concrete
     * @param string $method
     * @return mixed
     */
    public function call($concrete, $method);
}