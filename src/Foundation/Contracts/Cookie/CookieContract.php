<?php

namespace Foundation\Contracts\Cookie;

interface CookieContract
{
    /**
     * Check if a cookie already exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Get a cookie.
     *
     * @param string $key
     * @return string
     */
    public function get($key);

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
                        $secure = null, $httpOnly = true, $raw = false, $sameSite = null);

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
                            $secure = null, $httpOnly = true, $raw = false, $sameSite = null);

    /**
     * Expire the given cookie.
     *
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @return void
     */
    public function forget($name, $path = null, $domain = null);
}