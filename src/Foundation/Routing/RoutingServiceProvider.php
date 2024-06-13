<?php

namespace Foundation\Routing;

use Foundation\Http\Request;
use Foundation\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerRequest();
        $this->registerRedirector();
        $this->registerRoute();
    }

    /**
     * Register the request.
     *
     * @return void
     */
    protected function registerRequest()
    {
        $this->app->singleton('request', function () {
            return new Request;
        });
    }

    /**
     * Register the redirector.
     *
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector;

            $redirector->setSession($app['session']);
            $redirector->setRequest($app['request']);

            return $redirector;
        });
    }

    /**
     * Register the router.
     *
     * @return void
     */
    protected function registerRoute()
    {
        $this->app->singleton('route', function ($app) {
            $route = new Route;
            $route->setContainer($app);
            
            return $route;
        });
    }
}