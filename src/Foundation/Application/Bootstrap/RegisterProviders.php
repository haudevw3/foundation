<?php

namespace Foundation\Application\Bootstrap;

use Foundation\Contracts\Application\ApplicationContract;

class RegisterProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     * @return void
     */
    public function bootstrap(ApplicationContract $app)
    {
        $this->registerConfiguredProviders($app);
    }

    /**
     * Register all of the configured providers.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     * @return void
     */
    protected function registerConfiguredProviders(ApplicationContract $app)
    {
        $providers = $app['config']->get('app.providers');

        foreach ($providers as $provider) {
            new $provider($app);
        }
    }
}