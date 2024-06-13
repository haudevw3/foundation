<?php

namespace Foundation\Auth;

use Foundation\Contracts\Auth\UserContract;
use Foundation\Contracts\Auth\UserProviderContract;
use Foundation\Contracts\Database\QueryBuilder;

class DatabaseUserProvider implements UserProviderContract
{
    /**
     * The Query/Builder instance.
     *
     * @var \Foundation\Contracts\Database\QueryBuilder
     */
    protected $query;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new DatabaseUserProvider instance.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @param string $table
     * @return void
     */
    public function __construct(QueryBuilder $query, $table)
    {
        $this->query = $query;
        $this->table = $table;
    }

    /**
     * Retrieve a user by their unique id.
     *
     * @param mixed $id
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveById($id)
    {
        $user = $this->query->table($this->table)->find($id);

        return $this->createPendingUser($user);
    }

    /**
     * Retrieve a user by their unique id and "remember me" token.
     *
     * @param mixed $id
     * @param string $token
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveByToken($id, $token)
    {
        $user = $this->createPendingUser(
            $this->query->table($this->table)->find($id)
        );

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token) ? $user : null;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) == 1 &&
            array_key_exists('password', $credentials))) {
            return;
        }

        $bool = true;

        $query = $this->query->table($this->table);

        foreach ($credentials as $key => $value) {
            if ($key == 'password') {
                continue;
            }

            if ($bool) {
                $bool = false;
                $query->where($key, $value);
            } else {
                $query->andWhere($key, $value);
            }
        }

        // If user data is returned, we will proceed to store the user
        // in the storage class for further processing if necessary.
        $user = $query->first();

        return $this->createPendingUser($user);
    }

    /**
     * Create a new PendingUser instance.
     *
     * @param mixed $user
     * @return \Foundation\Auth\PendingUser|null
     */
    protected function createPendingUser($user)
    {
        if (! is_null($user)) {
            return new PendingUser($user);
        }
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param string $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $this->query->table($this->table)
                ->where($user->getAuthIdName(), $user->getAuthId())
                ->update([$user->getRememberTokenName() => $token]);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return password_verify(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}