<?php

namespace Foundation\Config\Contracts;

interface Repository
{
    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get the value of the configuration with the key.
     *
     * @param  array|string  $key
     * @param  mixed         $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed         $value
     * @return void
     */
    public function set($key, $value = null);

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value);

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function prepend($key, $value);

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all();
}