<?php

namespace Foundation\Routing;

use Foundation\Container\Container;
use Foundation\Support\Arr;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route
{
    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP method the route responds to.
     *
     * @var array
     */
    protected $methods;

    /**
     * The route action array.
     *
     * @var array
     */
    protected $action;

    /**
     * The array of matched parameters.
     *
     * @var array|null
     */
    protected $parameters;

    /**
     * The router instance used by the route.
     *
     * @var \Foundation\Routing\Router
     */
    protected $router;

    /**
     * The route URI instance used by the route.
     *
     * @var \Foundation\Routing\RouteUri
     */
    protected $routeUri;

    /**
     * The route where instance used by the route.
     *
     * @var \Foundation\Routing\RouteWhere
     */
    protected $routeWhere;

    /**
     * The container instance.
     *
     * @var \Foundation\Container\Container
     */
    protected $container;

    /**
     * Create a new route instance.
     *
     * @param  array|string    $methods
     * @param  string          $uri
     * @param  array|\Closure  $action
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction(Arr::except($action, ['prefix', 'middleware']));

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        $this->prefix(is_array($action) ? Arr::get($action, 'prefix', '') : '');
        $this->middleware(is_array($action) ? Arr::get($action, 'middleware', ['web']) : ['web']);
    }

    /**
     * Parse the route action into a standard array.
     *
     * @param  array|\Closure  $action
     * @return array
     * 
     * @throws \LogicException
     */
    protected function parseAction($action)
    {
        return RouteAction::parse($this->uri, $action);
    }

    /**
     * Get the prefix of the route.
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->action['prefix'] ?? null;
    }

    /**
     * Add a prefix to the route URI.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $prefix = $prefix ?? '';

        $this->updatePrefixOnAction($prefix);

        $uri = trim(trim($prefix, '/').'/'.trim($this->uri, '/'), '/');

        return $this->setUri($uri);
    }

    /**
     * Update the prefix attribute on the action array.
     *
     * @param  string  $prefix
     * @return void
     */
    protected function updatePrefixOnAction($prefix)
    {
        if (! empty($prefix)) {
            $prefix = trim(trim($prefix, '/').'/'.trim($this->action['prefix'] ?? '', '/'), '/');
        }
        
        $this->action['prefix'] = $prefix;
    }

    /**
     * Get the URI of the route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Set the URI attribute on the route.
     *
     * @param  string  $uri
     * @return $this
     */
    protected function setUri($uri)
    {
        $this->uri = $uri;

        $this->routeUri = RouteUri::parse($uri);

        return $this;
    }

    /**
     * Get the method of the route.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Set a regular expression requirement on the route.
     *
     * @param  array|string  $name
     * @param  string|null   $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        return $this->setWhere($name, $expression);
    }

    /**
     * Set the where attribute on the route.
     *
     * @param  array   $name
     * @param  string  $expression
     * @return $this
     */
    protected function setWhere($name, $expression = null)
    {
        $this->routeWhere = RouteWhere::parse($name, $expression);

        return $this;
    }

    /**
     * Set the middleware attached to the route.
     *
     * @param  array|string  $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (! is_array($middleware)) {
            $middleware = func_get_args();
        }

        foreach ($middleware as $index => $value) {
            $middleware[$index] = (string) $value;
        }

        $this->action['middleware'] = array_merge(
            (array) ($this->action['middleware'] ?? []), $middleware
        );

        return $this;
    }

    /**
     * Get the name of the route.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->action['name'] ?? null;
    }

    /**
     * Set the name attribute on the route.
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->action['name'] = $name;

        $this->router->getRoutes()->addToCollectionByName($this);

        return $this;
    }

    /**
     * Get the parameters of the route.
     *
     * @return array|null
     */
    public function getParameters()
    {
        return $this->parameters ?? null;
    }

    /**
     * Convert the route to a symfony route.
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function toSymfonyRoute()
    {
        return new SymfonyRoute(
            $this->routeUri->path(),
            $this->routeUri->getOptionalParameters(),
            $this->routeWhere->getRequirements(),
            ['utf8' => true, 'action' => $this->action],
            '', [], $this->methods()
        );
    }

    /**
     * Set the router instance on the route.
     *
     * @param  \Foundation\Routing\Router  $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Set the container instance on the route.
     *
     * @param  \Foundation\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}