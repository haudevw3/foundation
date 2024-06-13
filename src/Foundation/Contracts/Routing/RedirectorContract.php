<?php

namespace Foundation\Contracts\Routing;

interface RedirectorContract
{
    /**
     * Create a new redirect response to the previous location.
     *
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function back($status = 302, $headers = []);

    /**
     * Create a new redirect response to the given path.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return \Foundation\Http\RedirectResponse
     */
    public function to($path, $status = 302, $headers = [], $secure = null);

    /**
     * Create a new redirect response to an external URL.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function away($path, $status = 302, $headers = []);

    /**
     * Create a new redirect response to a named route.
     *
     * @param string $name
     * @param mixed $params
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function route($name, $params = [], $status = 302, $headers = []);
}