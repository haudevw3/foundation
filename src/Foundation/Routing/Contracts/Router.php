<?php

namespace Foundation\Routing\Contracts;

interface Router
{
    /**
     * Register a new GET route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function get($uri, $action);

    /**
     * Register a new POST route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function post($uri, $action);

    /**
     * Register a new PUT route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function put($uri, $action);

    /**
     * Register a new PATCH route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function patch($uri, $action);

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function options($uri, $action);

    /**
     * Register a new DELETE route with the router.
     *
     * @param  string          $uri
     * @param  array|callable  $action
     * @return \Foundation\Routing\Route
     */
    public function delete($uri, $action);

    /**
     * Create a route group with shared attributes.
     *
     * @param  array     $attributes
     * @param  \Closure  $routes
     * @return void
     */
    public function group($attributes, $routes);
}