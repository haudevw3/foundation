<?php

namespace Foundation\Routing;

use Foundation\Contracts\Routing\RedirectorContract;
use Foundation\Http\RedirectResponse;
use Foundation\Http\Request;
use Foundation\Session\Session;

class Redirector implements RedirectorContract
{
    /**
     * The request instance.
     *
     * @var \Foundation\Http\Request
     */
    protected $request;

    /**
     * The session instance.
     *
     * @var \Foundation\Session\Session
     */
    protected $session;

    /**
     * Create a new redirect response to the previous location.
     *
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function back($status = 302, $headers = [])
    {
        $this->to($this->session->previousUrl(), $status, $headers);

        return $this->createRedirect();
    }

    /**
     * Create a new redirect response to the given path.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return \Foundation\Http\RedirectResponse
     */
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        if (! empty($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }

        header("Location: $path", TRUE, $status);

        return $this->createRedirect();
    }

    /**
     * Create a new redirect response to an external URL.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function away($path, $status = 302, $headers = [])
    {
        if (! $this->isValidUrl($path)) {
            return $path;
        }

        return $this->to($path, $status, $headers);
    }

    /**
     * Determine if the given path is a valid URL.
     *
     * @param string $path
     * @return bool
     */
    protected function isValidUrl($path)
    {
        if (! preg_match('~^(#|//|https?://|(mailto|tel|sms):)~', $path)) {
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**
     * Create a new redirect response to a named route.
     *
     * @param string $name
     * @param mixed $params
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    public function route($name, $params = [], $status = 302, $headers = [])
    {
        $this->to(route($name, $params), $status, $headers);

        return $this->createRedirect();
    }

    /**
     * Create a new redirect response.
     *
     * @return \Foundation\Http\RedirectResponse
     */
    protected function createRedirect()
    {
        return tap(new RedirectResponse, function ($redirect) {
            $redirect->setSession($this->session);
            $redirect->setRequest($this->request);
        });
    }

    /**
     * Set the request instance.
     *
     * @param \Foundation\Http\Request $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Set the session instance.
     *
     * @param \Foundation\Session\Session $session
     * @return void
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }
}