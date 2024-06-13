<?php

namespace Foundation\Contracts\Auth;

interface AuthenticatorContract
{
    /**
     * Get the guard instance by its name.
     *
     * @param string|null $name
     * @return \Foundation\Auth\Contracts\GuardContract
     */
    public function guard($name = null);
}