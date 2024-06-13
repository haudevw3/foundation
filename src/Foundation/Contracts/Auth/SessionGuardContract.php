<?php

namespace Foundation\Contracts\Auth;

interface SessionGuardContract
{
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials, $remember = false);

    /**
     * Log a user into the application.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param bool $remember
     * @return void
     */
    public function login(UserContract $user, $remember = false);

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool $remember
     * @return bool|\Foundation\Contracts\Auth\UserContract
     */
    public function loginUsingId($id, $remember = false);

    /**
     * Log the given user ID and Token into the application.
     *
     * @param string $token
     * @return bool|\Foundation\Contracts\Auth\UserContract
     */
    public function loginUsingToken($token);

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout();

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember();
}