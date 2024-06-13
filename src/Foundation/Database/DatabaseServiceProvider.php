<?php

namespace Foundation\Database;

use Foundation\Database\Query\Builder;
use Foundation\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerConnection();
        $this->registerQueryBuilder();
    }

    /**
     * Register the connection instance.
     *
     * @return void
     */
    protected function registerConnection()
    {
        $this->app->singleton('db.connection', function () {
            return new Connection;
        });
    }

    /**
     * Register the query builder instance.
     *
     * @return void
     */
    protected function registerQueryBuilder()
    {
        $this->app->singleton('db', function ($app) {
            return new Builder($app['db.connection']);
        });
    }
}