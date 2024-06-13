<?php

namespace Foundation\Contracts\Auth;

interface UserProviderContract
{
    /**
     * Retrieve a user by their unique id.
     *
     * @param mixed $id
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveById($id);

    /**
     * Retrieve a user by their unique id and "remember me" token.
     *
     * @param mixed $id
     * @param string $token
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveByToken($id, $token);

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveByCredentials(array $credentials);

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param string $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token);

    /**
     * Validate a user against the given credentials.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials);
}
