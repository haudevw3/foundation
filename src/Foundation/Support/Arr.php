<?php

namespace Foundation\Support;

class Arr
{
    /**
     * Get the value with the key of a given array.
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    public static function get(array $array, $key)
    {
        $keys = explode('.', $key);

        $values = $array[$keys[0]];

        unset($keys[0]);

        foreach ($keys as $key) {
            if(isset($values[$key])) {
                $values = $values[$key];
            } else {
                return null;
            }
        }

        return $values;
    }

    /**
     * Set the value with the key of a given array.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(array &$array, $key, $value)
    {
        $keys = explode('.', $key);

        $temp = &$array;

        foreach ($keys as $key) {
            if (! isset($temp[$key])) {
                $temp[$key] = [];
            }

            $temp = &$temp[$key];
        }

        $temp = $value;
    }
}