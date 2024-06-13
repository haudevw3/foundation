<?php

namespace Foundation\Auth;

use Foundation\Contracts\Auth\AuthenticationException;
use Foundation\Contracts\Auth\UserContract;

trait GuardHelpers
{
    /**
     * The PendingUser instance.
     *
     * @var \Foundation\Contracts\Auth\UserContract
     */
    protected $user;

    /**
     * The DatabaseUserProvider instance.
     *
     * @var \Foundation\Contracts\Auth\UserProviderContract
     */
    protected $provider;

    /**
     * Determine if the current user is authenticated. If not, throw an exception.
     *
     * @return \Foundation\Contracts\Auth\UserContract
     *
     * @throws \Foundation\Contracts\Auth\AuthenticationException
     */
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException('Invalid user authentication.');
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return ! is_null($this->user);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthId();
        }
    }

    /**
     * Set the current user.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @return void
     */
    public function setUser(UserContract $user)
    {
        $this->user = $user;
    }
}