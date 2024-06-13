<?php

namespace Foundation\Auth;

use Foundation\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerAuthenticator();
    }

    /**
     * Register the Authenticator.
     *
     * @return void
     */
    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', function ($app) {
            return new Authenticator($app);
        });
    }
}