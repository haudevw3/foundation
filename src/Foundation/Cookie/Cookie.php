<?php

namespace Foundation\Cookie;

use Foundation\Contracts\Cookie\CookieContract;

class Cookie implements CookieContract
{
    /**
     * Check if a cookie already exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Get a cookie.
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->exists($key) ? $_COOKIE[$key] : '';
    }

    /**
     * Set a cookie.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return void
     */
    public function set($name, $value, $minutes = 0, $path = null, $domain = null, 
                        $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        $time = ($minutes == 0) ? 0 : (time() + ($minutes * 60));

        $options = [
            'expires'  => $time,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite
        ];

        setcookie($name, $value, $options);
    }

    /**
     * Set a cookie that lasts "forever" (five years).
     *
     * @param string $name
     * @param string $value
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return void
     */
    public function forever($name, $value, $path = null, $domain = null, 
                            $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        $this->set($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Expire the given cookie.
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public function forget($name, $path = null, $domain = null)
    {
        $this->set($name, null, -2628000, $path, $domain);
    }
}