<?php

namespace Foundation\Config;

use ArrayAccess;
use Foundation\Config\Contracts\Repository as RepositoryContract;
use Foundation\Support\Arr;

class Repository implements ArrayAccess, RepositoryContract
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::exists($this->items, $key);
    }

    /**
     * Get the value of the configuration with the key.
     *
     * @param  array|string  $key
     * @param  mixed         $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $keys = is_array($key) ? $key : [$key];

        $array = [];

        foreach ($keys as $key) {
            $array[$key] = Arr::get($this->items, $key, $default);
        }

        return $array;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed         $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $array = array_merge($array, is_array($value) ? $value : [$value]);

        $this->set($key, $array);
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}