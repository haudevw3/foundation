<?php

namespace Foundation\Http;

use Closure;
use Foundation\Contracts\Application\ApplicationContract;
use Foundation\Contracts\Http\KernelContract;
use Foundation\Routing\Route;

class Kernel implements KernelContract
{
    /**
     * The application implementation.
     *
     * @var \Foundation\Contracts\Application\ApplicationContract
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \Foundation\Routing\Route
     */
    protected $route;

    /**
     * The application's middleware stack.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     * @param \Foundation\Routing\Route $route
     * @return void
     */
    public function __construct(ApplicationContract $app, Route $route)
    {
        $this->app = $app;
        $this->route = $route;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param \Foundation\Http\Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        $route = $this->route->getByUri($request->uri() ?: '/');

        if (empty($route)) {
            return;
        }

        $action = $route['action'];
        
        if ($action instanceof Closure) {
            return $action();
        }
        
        $middleware = $route['middleware'];

        // Traverse through the application's middleware.
        // Then, proceed to the middleware for groups or routes, if any.
        if (empty($middleware)) {
            return $this->route->runRoute($route, $request);
        }

        $middleware = $this->getRouteMiddleware()[$middleware];

        $instance = new $middleware;

        $instance->handle($request, function ($request) use ($route) {
            return $this->route->runRoute($route, $request);
        });
    }

    /**
     * Determine if the kernel has a given middleware.
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddleware($middleware)
    {
        return in_array($middleware, $this->middleware);
    }

    /**
     * Get the application's route middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups()
    {
        return $this->middlewareGroups;
    }

    /**
     * Get the application's route middleware.
     *
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }
}