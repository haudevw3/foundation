<?php

namespace Foundation\Auth;

use Foundation\Contracts\Auth\GuardContract;
use Foundation\Contracts\Auth\SessionGuardContract;
use Foundation\Contracts\Auth\UserContract;
use Foundation\Contracts\Auth\UserProviderContract;
use Foundation\Contracts\Cookie\CookieContract;
use Foundation\Contracts\Http\RequestContract;
use Foundation\Contracts\Session\SessionContract;
use Foundation\Support\Str;

class SessionGuard implements GuardContract, SessionGuardContract
{
    use GuardHelpers;

    /**
     * The name of the guard.
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    protected $name;

    /**
     * The number of minutes that the "remember me" cookie should be valid for.
     *
     * @var int
     */
    protected $rememberDuration = 2628000;

    /**
     * The Request instance.
     *
     * @var \Foundation\Contracts\Http\RequestContract
     */
    protected $request;

    /**
     * The Session instance.
     *
     * @var \Foundation\Contracts\Session\SessionContract
     */
    protected $session;

    /**
     * The Cookie instance.
     *
     * @var \Foundation\Contracts\Cookie\CookieContract
     */
    protected $cookie;

    /**
     * Create a new SessionGuard instance.
     *
     * @param string $name
     * @param \Foundation\Contracts\Auth\UserProviderContract $provider
     * @param \Foundation\Contracts\Http\RequestContract $request
     * @param \Foundation\Contracts\Session\SessionContract $session
     * @param \Foundation\Contracts\Cookie\CookieContract $cookie
     * @return void
     */
    public function __construct($name, UserProviderContract $provider, RequestContract $request, 
                                SessionContract $session, CookieContract $cookie)
    {
        $this->name = $name;
        $this->provider = $provider;
        $this->request = $request;
        $this->session = $session;
        $this->cookie = $cookie;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Foundation\Contracts\Auth\UserContract|null
     */
    public function user()
    {
        // Assume the developer is using the validate method to verify the data.
        // We will return the data directly if the developer wants to retrieve the user data.
        // We do not want to repeat additional methods to fetch the data, avoiding unnecessary delays.
        if (! is_null($this->user)) {
            return $this->user;
        }

        // Here we will retrieve the ID of the previously
        // saved session if the user is logged in.
        $id = $this->getIdBySecretKey(
            $this->session->get('_id')
        );

        if (is_null($id)) {
            return;
        }

        // We will extract the user by ID and return the user's data if available.
        return $this->user = $this->provider->retrieveById($id);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials, $remember = false)
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        // Here we will check through the following requirements:
        // If the user value is null.
        // If the user does not have a password field and the value is not null.
        // If the user has a password field, the password must be verified and, of course, the value must not be null.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        return false;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param mixed $user
     * @param array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, array $credentials)
    {
        // If the user value is null.
        if (is_null($user)) {
            return false;
        }

        // If the user does not have a password field and the value is not null.
        if (! isset($credentials['password'])) {
            return true;
        }

        // If the user has a password field, the password must be verified and,
        // of course, the value must not be null.
        $validated = $this->provider->validateCredentials($user, $credentials);

        return $validated;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials)
    {
        $this->user = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Log a user into the application.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @param bool $remember
     * @return void
     */
    public function login(UserContract $user, $remember = false)
    {
        // Storing user sessions by their authenticated ID in the database.
        $this->updateSession($user->id);

        // If remember is true, we will generate a token, store it in the database, 
        // and set a cookie to maintain it for subsequent login attempts.
        if ($remember) {
            $this->updateCookie($user);
        }

        $this->setUser($user);
    }

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool $remember
     * @return bool|\Foundation\Contracts\Auth\UserContract
     */
    public function loginUsingId($id, $remember = false)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    /**
     * Log the given user Token into the application.
     *
     * @param string $token
     * @return bool|\Foundation\Contracts\Auth\UserContract
     */
    public function loginUsingToken($token)
    {
        if (! is_null($user = $this->provider->retrieveByToken(
            $this->getIdBySecretKey($token), $token
        ))) {
            $this->login($user, false);

            return $user;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        if (is_null($user)) {
            return;
        }

        // Clear the remember token of the user 
        // from the database and web browser.
        if (! empty($user->getRememberToken())) {
            $this->cookie->forget($user->getRememberToken());

            $this->provider->updateRememberToken($user, '');
        }

        $this->user = null;
        $this->session->remove('_id');
    }

    /**
     * Update the session with the given ID.
     *
     * @param string $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->set(
            '_id', $this->createSecretKeyById($id)
        );
    }

    /**
     * Update the cookie with the given user.
     *
     * @param \Foundation\Contracts\Auth\UserContract $user
     * @return void
     */
    protected function updateCookie(UserContract $user)
    {
        $token = $this->createSecretKeyById($user->id);

        $user->setRememberToken($token);

        $this->cookie->set(
            $user->getRememberTokenName(),
            $token,
            $this->getRememberDuration()
        );

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return $this->cookie->exists('remember_token');
    }

    /**
     * Get a id by secret key for session or cookie.
     *
     * @param string $key
     * @return mixed
     */
    public function getIdBySecretKey($key)
    {
        return explode($this->name, $key)[0];
    }

    /**
     * Create a secret key by id for session or cookie.
     *
     * @param mixed $id
     * @return void
     */
    public function createSecretKeyById($id)
    {
        return $id.$this->name.Str::random(32);
    }

    /**
     * Get the number of minutes the remember me cookie should be valid for.
     *
     * @return int
     */
    protected function getRememberDuration()
    {
        return $this->rememberDuration;
    }

    /**
     * Set the number of minutes the remember me cookie should be valid for.
     *
     * @param int $minutes
     * @return $this
     */
    protected function setRememberDuration($minutes)
    {
        $this->rememberDuration = $minutes;

        return $this;
    }
}