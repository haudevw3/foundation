<?php

namespace Foundation\Routing;

use Foundation\Routing\Exceptions\RouteNameAlreadyExistsException;
use Foundation\Support\Arr;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

class RouteCollection
{
    /**
     * All routes registered in the application.
     *
     * @var \Foundation\Routing\Route[]
     */
    protected $routes = [];

    /**
     * All routes registered by name in the application.
     *
     * @var \Foundation\Routing\Route[]
     */
    protected $nameList = [];

    /**
     * All routes registered by method in the application.
     *
     * @var array
     */
    protected $methodList = [];

    /**
     * Add the given route to the collection.
     *
     * @param  \Foundation\Routing\Route  $route
     * @return \Foundation\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addToCollection($route);

        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param  \Foundation\Routing\Route  $route
     * @return void
     */
    protected function addToCollection(Route $route)
    {
        $uri = $route->uri();

        foreach ($route->methods() as $method) {
            $this->routes[$method.$uri] = $route;
        }

        $this->methodList[$method][$uri] = $route;
    }

    /**
     * Add the given route to the collection by name.
     *
     * @param  \Foundation\Routing\Route  $route
     * @return void
     * 
     * @throws \Foundation\Routing\Exceptions\RouteNameAlreadyExistsException
     */
    public function addToCollectionByName(Route $route)
    {
        $name = $route->getName();

        if ($this->hasRouteName($name)) {
            throw new RouteNameAlreadyExistsException("The route name [$name] already exists.");
        }

        $this->nameList[$name] = $route;
    }

    /**
     * Convert the collection to a symfony route collection instance.
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function toSymfonyRouteCollection()
    {
        $symfonyRouteCollection = new SymfonyRouteCollection;

        foreach ($this->getRoutes() as $route) {
            $symfonyRouteCollection->add(
                $route->getName(), $route->toSymfonyRoute()
            );
        }

        return $symfonyRouteCollection;
    }

    /**
     * Determine if the route collection contains a given name route.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasRouteName($name)
    {
        return isset($this->nameList[$name]);
    }

    /**
     * Get a route instance by its name.
     *
     * @param  string  $name
     * @return \Foundation\Routing\Route|null
     */
    public function getByName($name)
    {
        return $this->hasRouteName($name) ? $this->nameList[$name] : null;
    }

    /**
     * Get routes from the collection by method.
     *
     * @param  string  $method
     * @return \Foundation\Routing\Route[]|null
     */
    public function getByMethod($method)
    {
        return isset($this->methodList[$method]) ? $this->methodList[$method] : null;
    }

    /**
     * Get all routes registered in the application.
     *
     * @return \Foundation\Routing\Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get all of the routes keyed by their name.
     *
     * @return \Foundation\Routing\Route[]
     */
    public function getRoutesByName()
    {
        return $this->nameList;
    }

    /**
     * Get all of the routes keyed by their HTTP method.
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->methodList;
    }
}