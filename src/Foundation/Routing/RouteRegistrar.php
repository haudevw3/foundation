<?php

namespace Foundation\Routing;

use BadMethodCallException;
use InvalidArgumentException;

class RouteRegistrar
{
    /**
     * The router instance.
     *
     * @var \Foundation\Routing\Router
     */
    protected $router;

    /**
     * The attributes to pass on to the router.
     *
     * @var array
     */
    protected $attributes;

    /**
     * The methods to dynamically pass through to the router.
     *
     * @var string[]
     */
    protected $passthru = ['get', 'post', 'put', 'delete'];

    /**
     * The attributes that can be set through this class.
     *
     * @var string[]
     */
    protected $allowedAttributes = ['prefix', 'middleware'];

    /**
     * Create a new route registrar instance.
     *
     * @param  \Foundation\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function group($callback)
    {
        $this->router->group($this->attributes, $callback);
    }

    /**
     * Set the value for a given attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value)
    {
        if (! in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [$key] does not exist.");
        }

        if ($key === 'prefix') {
            $value = (string) $value;
        }
        
        if ($key === 'middleware') {
            foreach ($value as $index => $middleware) {
                $value[$index] = (string) $middleware;
            }
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Register a new route with the router.
     *
     * @param  string          $method
     * @param  string          $uri
     * @param  array|\Closure  $action
     * @return \Foundation\Routing\Route
     */
    protected function registerRoute($method, $uri, $action)
    {
        return $this->router->{$method}($uri, array_merge($action, $this->attributes));
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Foundation\Routing\Route
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->passthru)) {
            return $this->registerRoute($method, ...$parameters);
        }

        if (in_array($method, $this->allowedAttributes)) {
            if ($method === 'middleware') {
                return $this->attribute(
                    $method, is_array($parameters[0]) ? $parameters[0] : $parameters
                );
            }

            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}