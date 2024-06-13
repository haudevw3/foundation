<?php

namespace Foundation\Contracts\Session;

interface SessionContract
{
    /**
     * Get the name of the session.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the name of the session.
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Start a session.
     *
     * @return void
     */
    public function start();

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Set the session ID.
     *
     * @param string $id
     * @return void
     */
    public function setId($id);

    /**
     * Flush the session data and regenerate the ID.
     *
     * @return void
     */
    public function regenerateSessionId();

    /**
     * Get the status of the session.
     *
     * @return string
     */
    public function status();

    /**
     * Get all of the session data.
     *
     * @return array
     */
    public function all();

    /**
     * Checks if a key exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Checks if a key is present and not null.
     *
     * @param string  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get an item from the session.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Set a key-value pair or array of key-value pairs in the session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Add a value to the session according to the given key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function push($key, $value);

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token();

    /**
     * Regenerate the CSRF token value.
     *
     * @return void
     */
    public function regenerateToken();

    /**
     * Removes an item from the session and returns its value.
     *
     * @param string $key
     * @return mixed
     */
    public function remove($key);

    /**
     * Remove one or many items from the session.
     *
     * @param string|array $keys
     * @return void
     */
    public function forget($keys);

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function clear();

    /**
     * Get the previous URL from the session.
     *
     * @return string
     */
    public function previousUrl();

    /**
     * Set the previous URL in the session.
     *
     * @param string $url
     * @return void
     */
    public function setPreviousUrl($url);
}