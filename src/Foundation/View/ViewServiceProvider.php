<?php

namespace Foundation\View;

use Foundation\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerView();
    }

    /**
     * Register the view.
     *
     * @return void
     */
    protected function registerView()
    {
        $this->app->singleton('view', function () {
            return new View;
        });
    }
}