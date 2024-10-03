<?php

namespace Foundation\Container\Contracts;

use Closure;

interface Dispatcher
{
    /**
     * Register a new before resolving callback for all types.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function beforeResolving(Closure $callback);

    /**
     * Register a new resolving callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function resolving(Closure $callback);

    /**
     * Register a new after resolving callback for all types.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function afterResolving(Closure $callback);
}