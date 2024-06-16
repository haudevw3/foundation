<?php

namespace Foundation\Support;

abstract class ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Foundation\Contracts\Application\ApplicationContract
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->boot();
        
        $this->register();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Load the given routes file if routes are not already cached.
     *
     * @param string $path
     * @return void
     */
    protected function loadRoutesFrom($path)
    {
        require base_path($path);
    }

    /**
     * Register a view file namespace.
     *
     * @param string $path
     * @param string $namespace
     * @return void
     */
    protected function loadViewsFrom($path, $namespace)
    {
        $config = $this->app->get('config');
        
        if ($config->has('view')) {
            $config->push('view.paths', [$namespace => $path]);
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app->get('config');

        $config->set($key, require base_path($path));
    }
}