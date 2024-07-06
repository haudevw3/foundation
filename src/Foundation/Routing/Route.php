<?php

namespace Foundation\Routing;

use Closure;
use Foundation\Container\Container;
use Foundation\Contracts\Routing\RouteContract;
use Foundation\Contracts\Routing\RouteNameAlreadyExistsException;
use Foundation\Http\Request;
use Foundation\Macroable\Traits\Macroable;

class Route implements RouteContract
{
    use Macroable, RouteBinding, RouteCollection;

    /**
     * The URI pattern for the route.
     *
     * @var string
     */
    protected $uri;

    /**
     * Indicates whether the current route is part of a group call.
     *
     * @var bool
     */
    protected $isCallGroup;

    /**
     * The container instance used by the route.
     *
     * @var \Foundation\Container\Container
     */
    protected $container;

    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);

        return $this;
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);

        return $this;
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);

        return $this;
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param array|\Closure $action
     * @return $this
     */
    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Add a route with the specified method, URI, and action to the route bindings.
     *
     * @param string $method
     * @param string $uri
     * @param array|\Closure $action
     * @return void
     */
    protected function addRoute($method, $uri, $action)
    {
        $this->bind('uri', $this->setUri(
            $this->getPrefix() ? ($this->getPrefix().'/'.trim($uri)) : trim($uri)
        )->uri());

        $this->bind('method', $method);
        $this->bind('action', $action);
        $this->bind('params', RouteUri::parseParams($this->uri()));
        $this->bind('replace', $this->getParams() ? RouteCompiled::replace(
            $this->getParams(),
            $this->uri()
        ) : null);
        $this->bind('pattern', $this->getParams() ? RouteCompiled::pattern(
            $this->getParams(),
            $this->uri()
        ) : null);

        if ($action instanceof Closure) {
            $this->addToCollections($this->getBindings());
        }
    }

    /**
     * Get parameters from binding.
     *
     * @return array|null
     */
    protected function getParams()
    {
        return $this->getBinding('params');
    }

    /**
     * Add a prefix to the route URI.
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->bind('prefix', trim($prefix, '/'));

        return $this;
    }

    /**
     * Get the prefix of the route.
     *
     * @return string|null
     */
    protected function getPrefix()
    {
        return $this->getBinding('prefix') ?? null;
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    protected function uri()
    {
        return $this->uri;
    }

    /**
     * Set URI for the route.
     *
     * @param string $uri
     * @return $this
     */
    protected function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Set the middleware attached to the route.
     *
     * @param array|string $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        $this->bind('middleware', $middleware);

        return $this;
    }

    /**
     * Get the middleware attached to the route.
     *
     * @return array|string|null
     */
    protected function getMiddleware()
    {
        return $this->getBinding('middleware') ?? null;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param \Closure $routes
     * @return void
     */
    public function group($routes)
    {
        $this->isCallGroup = true;

        $routes();

        $this->isCallGroup = false;
        $this->bind('prefix');
        $this->bind('middleware');
    }

    /**
     * Check if the current route is part of a group call.
     *
     * @return bool
     */
    protected function isCallGroup()
    {
        return $this->isCallGroup;
    }

    /**
     * Add the route name.
     *
     * @param string $name
     * @return void
     */
    public function name($name)
    {
        if ($this->hasNamedRoute($name)) {
            throw new RouteNameAlreadyExistsException("The route name [$name] already exists.");
        }

        $this->bind('name', $name);

        $this->addToCollections($this->getBindings());
        
        if ($this->isCallGroup()) {
            $this->keepOnlyBindings(
                [
                    $this->getPrefix() ? 'prefix' : null,
                    $this->getMiddleware() ? 'middleware' : null
                ]
            );
        } else {
            $this->resetBindings();
        }
    }

    /**
     * Set a regular expression requirement on the route.
     * 
     * @param string|array $name
     * @param string|null $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        $this->setWhere($this->parseWhere($name, $expression));

        return $this;
    }

    /**
     * Set a regular expression for the route.
     *
     * @param array $wheres
     * @return void
     */
    protected function setWhere($wheres)
    {
        $explode = explode('/', $this->uri());

        $pattern = implode('\/', $explode);

        foreach ($wheres as $name => $expression) {
            $pattern = preg_replace('/\{'.$name.'\?\}/', $expression, $pattern);
        }

        $this->bind('pattern');
        $this->bind('where', "/^$pattern$/");
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param array|string $name
     * @param string|null $expression
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        if (is_null($expression)) {
            $expression = '([^\/]+)';
        }

        if (is_array($name)) {
            foreach ($name as $value) {
                $wheres[$value] = $expression;
            }

            return $wheres;
        }

        return [$name => $expression];
    }

    /**
     * Return the response for the given route.
     *
     * @param array $route
     * @param \Foundation\Http\Request $request
     * @return void
     */
    public function runRoute($route, Request $request)
    {
        if (! $request->isMethod($route['method'])) {
            return;
        }

        if (! empty($route['params'])) {
            $this->parseParamsFromRequest($route, $request);

            $request->setParamRouteForRequest($route['params']);
        }

        $this->runController($route);
    }

    /**
     * Executes the specified controller based on the given route.
     *
     * @param array $route
     * @return void
     */
    protected function runController($route)
    {
        $action = $route['action'];
        $controller = $action[0];
        $method = $action[1];

        $this->container->build($controller);
        $this->container->call($controller, $method);
    }

    /**
     * Parse a request URI and a route URI pattern to extract parameter values 
     * from the request URI according to the specified route pattern.
     *
     * @param array $route
     * @param \Foundation\Http\Request $request
     * @return void
     */
    protected function parseParamsFromRequest(&$route, Request $request)
    {
        preg_match($route['where'] ?: $route['pattern'], $request->uri(), $matches);

        unset($matches[0]);

        $i = 1;

        foreach ($route['params'] as $key => $value) {
            $route['params'][$key] = $matches[$i];

            $i++;
        }
    }

    /**
     * Set the container instance on the route.
     *
     * @param \Foundation\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}