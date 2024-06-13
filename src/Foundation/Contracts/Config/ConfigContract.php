<?php

namespace Foundation\Contracts\Config;

interface ConfigContract
{
    /**
     * Check if the configuration item exists or not.
     *
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Set a given configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all();

    /**
     * Adds a value to the end of the array of the given configuration by key.
     *
     * @param string $key
     * @param array|string $value
     * @return void
     */
    public function push($key, $value);

    /**
     * Adds a value to the beginning of the array of the given configuration by key.
     *
     * @param string $key
     * @param array|string $value
     * @return void
     */
    public function prepend($key, $value);
}