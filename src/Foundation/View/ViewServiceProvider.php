<?php

namespace Foundation\View;

use Foundation\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerView();
        $this->registerTemplate();
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

    /**
     * Register the template.
     *
     * @return void
     */
    protected function registerTemplate()
    {
        $this->app->singleton('template', function () {
            return new Template;
        });
    }
}