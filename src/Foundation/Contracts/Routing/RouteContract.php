<?php

namespace Foundation\Contracts\Routing;

interface RouteContract
{
    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function get($uri, $action);

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function post($uri, $action);

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function put($uri, $action);

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function delete($uri, $action);

    /**
     * Add a prefix to the route URI.
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix($prefix);

    /**
     * Set the middleware attached to the route.
     *
     * @param array|string $middleware
     * @return $this
     */
    public function middleware($middleware);

    /**
     * Create a route group with shared attributes.
     *
     * @param \Closure $routes
     * @return void
     */
    public function group($routes);

    /**
     * Add the route name.
     *
     * @param string $name
     * @return void
     */
    public function name($name);
}