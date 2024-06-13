<?php

namespace Foundation\Contracts\Http;

interface RedirectResponseContract
{
    /**
     * Flash a piece of data to the session.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function with($key, $value);

    /**
     * Flash an array of input to the session.
     *
     * @param array|null $input
     * @return $this
     */
    public function withInput(array $input = null);

    /**
     * Flash a container of errors to the session.
     *
     * @param array|null $error
     * @return $this
     */
    public function withErrors(array $error = null);
}