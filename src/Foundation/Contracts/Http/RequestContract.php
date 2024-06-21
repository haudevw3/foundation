<?php

namespace Foundation\Contracts\Http;

interface RequestContract
{
    /**
     * Get the input parameters from the current request.
     *
     * @return array
     */
    public function getParamFromRequest();

    /**
     * Get the parameters from the route and pass them to the request.
     *
     * @param array $params
     * @return void
     */
    public function setParamRouteForRequest($params = []);

    /**
     * Get the method from the current request.
     *
     * @return string
     */
    public function method();

    /**
     * Check if the request's HTTP method is a specific method.
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method);

    /**
     * 
     * Get information about the path of the current request. No domain, no query string.
     *
     * @return string
     */
    public function path();

    /**
     * Get the URI from the current request.
     *
     * @return string
     */
    public function uri();

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function url();

    /**
     * Get query string from current request.
     *
     * @return string
     */
    public function queryString();

    /**
     * 
     * Get the scheme and domain from the current request.
     *
     * @return string
     */
    public function domain();

    /**
     * Get path root on computer of client from the request.
     *
     * @return string
     */
    public function root();

    /**
     * Get ip of client from the request.
     *
     * @return string
     */
    public function ip();

    /**
     * Get all the request's input data.
     *
     * @return array
     */
    public function all();

    /**
     * Check whether the given input key exists or not.
     *
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Check if multiple given input keys exist or not.
     *
     * @param array $keys
     * @return bool
     */
    public function hasMany($keys);

    /**
     * 
     * Check if the uploaded file exists with the given key.
     *
     * @param string $key
     * @return bool
     */
    public function hasFile($key);

    /**
     * 
     * Get an the specified keyword input value.
     *
     * @param string $key
     * @return mixed
     */
    public function input($key);

    /**
     * 
     * Get input values ​​from specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function only($keys);

    /**
     * Get all input values ​​except specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function except($keys);

    /**
     * Get the uploaded file with the given input key.
     *
     * @param string $key
     * @return mixed
     */
    public function file($key);

    /**
     * Get the value of a parameter from the query string.
     *
     * @param string $key
     * @return string
     */
    public function query($key);

    /**
     * Verify the input data to see if it complies with the given rules.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $default
     * @return mixed
     */
    public function validate(array $data, array $rules, array $messages, array $default = null);
}