<?php

namespace Foundation\Cookie;

use Foundation\Support\ServiceProvider;

class CookieServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerCookie();
    }

    /**
     * Register the cookie instance.
     *
     * @return void
     */
    protected function registerCookie()
    {
        $this->app->singleton('cookie', function () {
            return new Cookie;
        });
    }
}