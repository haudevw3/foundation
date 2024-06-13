<?php

namespace Foundation\Routing;

trait RouteCollection
{
    /**
     * All routes registered in the application.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Add a route to the route collection.
     *
     * @param array $route
     * @return void
     */
    protected function addToCollections($route)
    {
        $this->routes[$route['uri']] = $route;
    }

    /**
     * Update a value in the collection based on the given key.
     *
     * @param string $uri
     * @param array $array
     * @return void
     */
    protected function updateToCollectionByKey($uri, $array)
    {
        foreach ($array as $key => $value) {
            $this->routes[$uri][$key] = $value;
        }
    }

    /**
     * Check if a route with the given name exists.
     *
     * @param string $name
     * @return bool
     */
    protected function hasNamedRoute($name)
    {
        foreach ($this->getRoutes() as $route) {
            return ($route['name'] == $name) ? true : false;
        }
    }

    /**
     * Get all routes registered in the application.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get a route by its name.
     *
     * @param string $name
     * @return array|null
     */
    public function getByName($name)
    {
        foreach ($this->getRoutes() as $route) {
            if ($route['name'] == $name) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Get a route by its URI.
     *
     * @param string $uri
     * @return array|null
     */
    public function getByUri($uri)
    {
        if (isset($this->getRoutes()[$uri])) {
            return $this->getRoutes()[$uri];
        }

        foreach ($this->getRoutes() as $key => $route) {
            $pattern = $route['pattern'];
            $where = $route['where'];

            if ((! empty($pattern) && preg_match($pattern, $uri)) ||
                (! empty($where) && preg_match($where, $uri))) {
                return $route;
            }
        }
        
        return null;
    }
}