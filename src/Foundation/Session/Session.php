<?php

namespace Foundation\Session;

use Foundation\Contracts\Session\SessionContract;
use Foundation\Support\Str;

class Session implements SessionContract
{
    /**
     * Get the name of the session.
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Set the name of the session.
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        session_name($name);
    }

    /**
     * Start a session.
     *
     * @return void
     */
    public function start()
    {
        session_start();
    }

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Set the session ID.
     *
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        session_id($id);
    }

    /**
     * Flush the session data and regenerate the ID.
     *
     * @return void
     */
    public function regenerateSessionId()
    {
        $this->setId(Str::random(20));
    }

    /**
     * Get the status of the session.
     *
     * @return string
     */
    public function status()
    {
        $status = '';

        if (session_status() == PHP_SESSION_DISABLED) {
            $status = 'DISABLED';
        } else if (session_status() == PHP_SESSION_NONE) {
            $status = 'NONE';
        } else if (session_status() == PHP_SESSION_ACTIVE) {
            $status = 'ACTIVE';
        }

        return $status;
    }

    /**
     * Get all of the session data.
     *
     * @return array
     */
    public function all()
    {
        $result = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->status()
        ];

        $result = array_merge($result, $_SESSION);

        return $result;
    }

    /**
     * Checks if a key exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Checks if a key is present and not null.
     *
     * @param string  $key
     * @return bool
     */
    public function has($key)
    {
        return ! empty($this->get($key));
    }

    /**
     * Set a key-value pair or array of key-value pairs in the session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get an item from the session.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->exists($key) ? $_SESSION[$key] : null;
    }

    /**
     * Add a value to the session according to the given key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function push($key, $value)
    {
        $values = $this->get($key);

        $result = is_array($values) ? array_push($values, $value) : [$values, $value];

        $this->set($key, (is_array($result) ? $result : $values));
    }

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     *
     * @return void
     */
    public function regenerateToken()
    {
        $this->set('_token', Str::random(20));
    }

    /**
     * Removes an item from the session and returns its value.
     *
     * @param string $key
     * @return mixed
     */
    public function remove($key)
    {
        $value = $this->get($key);

        if (! empty($value)) {
            $this->forget($key);
        }

        return $value;
    }

    /**
     * Remove one or many items from the session.
     *
     * @param string|array $keys
     * @return void
     */
    public function forget($keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];

        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Consume a given session and return its value.
     *
     * @param string $default
     * @param string $key
     * @return mixed
     */
    public function consume($default, $key)
    {
        $result = $this->get($default);

        if (! isset($result[$key])) {
            return null;
        }

        $value = $result[$key];

        unset($result[$key]);

        $this->set($default, $result);

        return $value;
    }

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function clear()
    {
        session_unset();
    }

    /**
     * Get the previous URL from the session.
     *
     * @return string
     */
    public function previousUrl()
    {
        return $this->get('previous_url')[0];
    }

    /**
     * Set the previous URL in the session.
     *
     * @param string $url
     * @return void
     */
    public function setPreviousUrl($url)
    {
        $url = '/'.trim($url, '/');

        if (! $this->exists('previous_url')) {
            $this->set('previous_url', [$url]);
        } else {
            $this->push('previous_url', $url);

            if (count($this->get('previous_url')) > 2) {

                $previousUrl = $this->get('previous_url');

                unset($previousUrl[0]);

                $this->set('previous_url', array_values($previousUrl));
            }
        }
    }
}