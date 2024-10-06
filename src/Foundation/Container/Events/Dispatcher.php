<?php

namespace Foundation\Container\Events;

use Closure;
use Foundation\Container\Contracts\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    /**
     * The abstract of the concrete instance.
     *
     * @var string|null 
     */
    protected $abstract;

    /**
     * All of the global before resolving callbacks.
     *
     * @var \Closure[]
     */
    protected $globalBeforeResolvingCallbacks = [];

    /**
     * All of the global resolving callbacks.
     *
     * @var \Closure[]
     */
    protected $globalResolvingCallbacks = [];

    /**
     * All of the global after resolving callbacks.
     *
     * @var \Closure[]
     */
    protected $globalAfterResolvingCallbacks = [];

    /**
     * All of the before resolving callbacks by class type.
     *
     * @var array
     */
    protected $beforeResolvingCallbacks = [];

    /**
     * All of the resolving callbacks by class type.
     *
     * @var array
     */
    protected $resolvingCallbacks = [];

    /**
     * All of the after resolving callbacks by class type.
     *
     * @var array
     */
    protected $afterResolvingCallbacks = [];

    /**
     * Add an event.
     *
     * @param  string|null  $abstract
     * @return $this
     */
    public function add($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Register a new before resolving callback for all types.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function beforeResolving(Closure $callback)
    {
        if (is_null($this->abstract)) {
            $this->globalBeforeResolvingCallbacks[] = $callback;
        } else {
            $this->beforeResolvingCallbacks[$this->abstract][] = $callback;
        }
    }

    /**
     * Register a new resolving callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function resolving(Closure $callback)
    {
        if (is_null($this->abstract)) {
            $this->globalResolvingCallbacks[] = $callback;
        } else {
            $this->resolvingCallbacks[$this->abstract][] = $callback;
        }
    }

    /**
     * Register a new after resolving callback for all types.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function afterResolving(Closure $callback)
    {
        if (is_null($this->abstract)) {
            $this->globalAfterResolvingCallbacks[] = $callback;
        } else {
            $this->afterResolvingCallbacks[$this->abstract][] = $callback;
        }
    }

    /**
     * Fire all of the before resolving callbacks.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return void
     */
    public function fireBeforeResolvingCallbacks($abstract, $parameters = [])
    {
        $this->fireBeforeCallbackArray($abstract, $parameters, $this->globalBeforeResolvingCallbacks);

        foreach ($this->beforeResolvingCallbacks as $type => $callbacks) {
            if ($type === $abstract || is_subclass_of($abstract, $type)) {
                $this->fireBeforeCallbackArray($abstract, $parameters, $callbacks);
            }
        }
    }

    /**
     * Fire all of the resolving callbacks.
     *
     * @param  string  $abstract
     * @param  mixed   $object
     * @return void
     */
    public function fireResolvingCallbacks($abstract, $object)
    {
        $this->fireCallbackArray($object, $this->globalResolvingCallbacks);

        $this->fireCallbackArray(
            $object, $this->getCallbacksForType($abstract, $object, $this->resolvingCallbacks)
        );

        $this->fireAfterResolvingCallbacks($abstract, $object);
    }

    /**
     * Fire all of the after resolving callbacks.
     *
     * @param  string  $abstract
     * @param  mixed   $object
     * @return void
     */
    public function fireAfterResolvingCallbacks($abstract, $object)
    {
        $this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);

        $this->fireCallbackArray(
            $object, $this->getCallbacksForType($abstract, $object, $this->afterResolvingCallbacks)
        );
    }

    /**
     * Get all callbacks for a given type.
     *
     * @param  string  $abstract
     * @param  object  $object
     * @param  array   $callbacksPerType
     * @return array
     */
    public function getCallbacksForType($abstract, $object, array $callbacksPerType)
    {
        $results = [];

        foreach ($callbacksPerType as $type => $callbacks) {
            if ($type === $abstract || $object instanceof $type) {
                $results = array_merge($results, $callbacks);
            }
        }

        return $results;
    }

    /**
     * Fire an array of callbacks with an object.
     *
     * @param  mixed  $object
     * @param  array  $callbacks
     * @return void
     */
    public function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($object, $this);
        }
    }

    /**
     * Fire an array of callbacks with an object.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @param  array   $callbacks
     * @return void
     */
    public function fireBeforeCallbackArray($abstract, $parameters, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($abstract, $parameters, $this);
        }
    }

    /**
     * Flush the event of all bindings.
     *
     * @return void
     */
    public function flush()
    {
        $this->abstract = null;
        $this->beforeResolvingCallbacks = [];
        $this->resolvingCallbacks = [];
        $this->afterResolvingCallbacks = [];
        $this->globalBeforeResolvingCallbacks = [];
        $this->globalResolvingCallbacks = [];
        $this->globalAfterResolvingCallbacks = [];
    }
}