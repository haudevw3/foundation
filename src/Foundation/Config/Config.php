<?php

namespace Foundation\Config;

use ArrayAccess;
use Foundation\Contracts\Config\ConfigContract;
use Foundation\Support\Arr;

class Config implements ArrayAccess, ConfigContract
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Check if the configuration item exists or not.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->items[$key]);
    }

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return Arr::get($this->items, $key);
    }

    /**
     * Set a given configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);
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
     * Adds a value to the end of the array of the given configuration by key.
     *
     * @param string $key
     * @param array|string $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $value = is_array($value) ? $value : [$value];

        $value = array_merge($array, $value);

        $this->set($key, $value);
    }

    /**
     * Adds a value to the beginning of the array of the given configuration by key.
     *
     * @param string $key
     * @param array|string $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        $value = is_array($value) ? $value : [$value];

        $array = array_merge($value, $array);

        $this->set($key, $array);
    }

    /**
     * Check if the configuration item exists or not.
     *
     * @param string $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a given configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Set a given configuration value by null.
     *
     * @param string $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}