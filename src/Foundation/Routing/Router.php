<?php

namespace Foundation\Routing;

use Closure;
use Foundation\Container\Container;
use Foundation\Routing\Contracts\Router as RouterContract;
use Foundation\Macroable\Traits\Macroable;

/**
 * @mixin \Foundation\Routing\RouteRegistrar
 */
class Router implements RouterContract
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The route collection instance.
     *
     * @var \Foundation\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The container instance.
     *
     * @var \Foundation\Container\Container
     */
    protected $container;

    /**
     * The currently dispatched route instance.
     *
     * @var \Foundation\Routing\Route
     */
    protected $currentRoute;

    /**
     * The request currently being dispatched.
     *
     * @var \Foundation\Http\Request
     */
    protected $currentRequest;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    public $middlewarePriority = [];

    /**
     * All of the verbs supported by the router.
     *
     * @var string[]
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Create new a router instance.
     * 
     * @param  \Foundation\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routes = new RouteCollection;
    }

    /**
     * Register a new GET route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function patch($uri, $action)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function options($uri, $action)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array     $attributes
     * @param  \Closure  $routes
     * @return void
     */
    public function group($attributes, $routes)
    {
        // At here we update the group attributes into the group stack.
        // Merge the new group attributes with the old group attributes.
        // After we will format and return a new result.
        $this->updateGroupStack($attributes);

        // We'll load the provided routes and merge the route action with
        // a new result the above (this is the last group attributes of the group stack).
        $this->loadRoutes($routes);

        // After we'll pop the last group attributes of the group stack.
        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack($attributes)
    {
        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param  array  $new
     * @return array
     */
    protected function mergeWithLastGroup($new)
    {
        return RouteGroup::merge($new, end($this->groupStack));
    }

    /**
     * Load the provided routes.
     *
     * @param  \Closure  $routes
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            (new RouteFileRegistrar($this))->register($routes);
        }
    }

    /**
     * Add a route to the route collection.
     *
     * @param  string          $method
     * @param  string          $uri
     * @param  array|\Closure  $action
     * @return \Foundation\Routing\Route
     */
    protected function addRoute($methods, $uri, $action)
    {
        return $this->routes->add($this->newRoute(
            $methods, $uri, array_merge($action, $this->getLastGroupStack())
        ));
    }

    /**
     * Create a new route object.
     *
     * @param  string          $method
     * @param  string          $uri
     * @param  array|\Closure  $action
     * @return \Foundation\Routing\Route
     */
    protected function newRoute($method, $uri, $action)
    {
        return (new Route($method, $uri, $action))
                ->setRouter($this)
                ->setContainer($this->container);
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    protected function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    protected function getGroupStack()
    {
        return $this->groupStack;
    }

    /**
     * Get the last group on the stack.
     *
     * @return array
     */
    protected function getLastGroupStack()
    {
        return $this->hasGroupStack() ? end($this->groupStack) : [];
    }

    /**
     * Get the route collection instance.
     *
     * @return \Foundation\Routing\RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($method === 'middleware') {
            return (new RouteRegistrar($this))->attribute(
                $method, is_array($parameters[0]) ? $parameters[0] : $parameters
            );
        }

        return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
    }
}