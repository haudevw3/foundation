<?php

namespace Foundation\Container;

use Foundation\Container\Contracts\ContextualBinder as ContextualBinderContract;
use Foundation\Support\Arr;

class ContextualBinder implements ContextualBinderContract
{
    /**
     * The concrete instance.
     *
     * @var string
     */
    protected $concrete;

    /**
     * The abstract target.
     *
     * @var string
     */
    protected $abstract;

    /**
     * The contextual binding map.
     *
     * @var array
     */
    protected $contextual = [];

    /**
     * The container instance.
     *
     * @var \Foundation\Container\Container
     */
    protected $container;

    /**
     * Create a new contextual binder.
     *
     * @param  \Foundation\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add a contextual binding.
     *
     * @param  array|string  $concrete
     * @return $this
     */
    public function add($concrete)
    {
        $this->concrete = $concrete;

        return $this;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param  array|string|\Closure  $implementation
     * @return void
     */
    public function give($implementation)
    {
        $abstract = $this->container->getAlias($this->abstract);

        foreach (Arr::wrap($this->concrete) as $concrete) {
            $this->contextual[$concrete][$abstract] = $implementation;
        }
    }

    /**
     * Define tagged services to be used as the implementation for the contextual binding.
     *
     * @param  string  $tag
     * @return void
     */
    public function giveTagged($tag)
    {
        $this->give(function ($container) use ($tag) {
            $taggedServices = $container->tagged($tag);

            return is_array($taggedServices) ? $taggedServices : iterator_to_array($taggedServices);
        });
    }

    /**
     * Specify the configuration item to bind as a primitive.
     *
     * @param  string       $key
     * @param  string|null  $default
     * @return void
     */
    public function giveConfig($key, $default = null)
    {
        $this->give(function ($container) use ($key, $default) {
            return $container->get('config')->get($key, $default);
        });
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @param  string           $concrete
     * @param  string|callable  $abstract
     * @return mixed
     */
    public function find($concrete, $abstract)
    {
        return $this->contextual[$concrete][$abstract] ?? null;
    }
}