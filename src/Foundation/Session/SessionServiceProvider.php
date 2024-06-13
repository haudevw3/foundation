<?php

namespace Foundation\Session;

use Foundation\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerSession();
    }

    /**
     * Register the session manager instance.
     *
     * @return void
     */
    protected function registerSession()
    {
        $this->app->singleton('session', function () {
            return new Session;
        });
    }
}