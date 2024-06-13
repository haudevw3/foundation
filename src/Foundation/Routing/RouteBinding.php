<?php

namespace Foundation\Routing;

trait RouteBinding
{
    /**
     * Contains the bindings of a route.
     *
     * @var array
     */
    protected $bindings = [
        'name'         => '',
        'uri'          => '',
        'method'       => '',
        'action'       => '',
        'params'       => '',
        'prefix'       => '',
        'middleware'   => '',
        'where'        => '',
        'replace'      => '',
        'pattern'      => '',
    ];

    /**
     * Check if a value is bound to a given key in the route bindings.
     *
     * @param string $key
     * @return bool
     */
    protected function bound($key)
    {
        return empty($this->bindings[$key]);
    }

    /**
     * Bind a value to a specific key within the route bindings.
     *
     * @param string $key
     * @param array|string|\Closure $value
     * @return void
     */
    protected function bind($key, $value = '')
    {
        $this->bindings[$key] = $value;
    }

    /**
     * Get all route bindings.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Retrieve the value bound to a given key in the route bindings.
     *
     * @param string $key
     * @return array|string|null|\Closure
     */
    protected function getBinding($key)
    {
        return ! $this->bound($key) ? $this->bindings[$key] : null;
    }

    /**
     * Reset all bindings to empty values.
     *
     * @return void
     */
    protected function resetBindings()
    {
        foreach ($this->getBindings() as $key => $value) {
            $this->bind($key, '');
        }
    }

    /**
     * Keeps only the specified bindings.
     *
     * @param array|string $excepts
     * @return void
     */
    protected function keepOnlyBindings($names)
    {
        $names = is_array($names) ? $names : [$names];

        foreach ($this->getBindings() as $key => $value) {
            if (in_array($key, $names)) {
                continue;
            }

            $this->bind($key);
        }
    }
}