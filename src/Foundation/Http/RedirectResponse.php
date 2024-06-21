<?php

namespace Foundation\Http;

use Foundation\Contracts\Http\RedirectResponseContract;
use Foundation\Session\Session;

class RedirectResponse implements RedirectResponseContract
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
     * Flash a piece of data to the session.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function with($key, $value)
    {
        $this->session->set($key, $value);

        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param array|null $input
     * @return $this
     */
    public function withInput(array $input = null)
    {
        $result = $this->request->all();
        
        if (! is_null($input)) {
            $this->removeItems($result, $input);
        }

        $this->session->set('olds', $result);

        return $this;
    }

    /**
     * Remove unnecessary items.
     *
     * @param array $items
     * @param array $contains
     * @return void
     */
    protected function removeItems(array &$items, array $contains)
    {
        foreach ($items as $key => $value) {
            if (! in_array($key, $contains)) {
                unset($items[$key]);
            }
        }
    }

    /**
     * Flash a container of errors to the session.
     *
     * @param array|null $error
     * @return $this
     */
    public function withErrors(array $error = null)
    {
        if (! is_null($error)) {
            $result = $this->session->get('errors');

            $this->removeItems($result, $error);

            $this->session->set('errors', $result);
        }

        return $this;
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