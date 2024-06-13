<?php

namespace Foundation\Auth;

use Foundation\Contracts\Application\ApplicationContract;
use Foundation\Contracts\Auth\AuthenticatorContract;
use InvalidArgumentException;

class Authenticator implements AuthenticatorContract
{
    /**
     * The Application instance
     *
     * @var \Foundation\Contracts\Application\ApplicationContract
     */
    protected $app;

    /**
     * The SessionGuard instance.
     *
     * @var \Foundation\Auth\SessionGuard
     */
    protected $sessionGuard;

    /**
     * The JsonWebToken instance.
     *
     * @var \Foundation\Auth\JsonWebToken
     */
    protected $jsonWebToken;

    /**
     * Create a new Authenticator instance.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     */
    public function __construct(ApplicationContract $app)
    {
        $this->app = $app;
    }

    /**
     * Get the guard instance by its name.
     *
     * @param string|null $name
     * @return \Foundation\Auth\SessionGuard|\Foundation\Auth\JsonWebToken
     */
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->resolve($name);
    }

    /**
     * Resolve the given guard.
     *
     * @param string $name
     * @return \Foundation\Auth\SessionGuard|\Foundation\Auth\JsonWebToken
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [$name] is not defined.");
        }

        if ($name === 'web') {
            return $this->createSessionDriver($name, $config);
        }
    }

    /**
     * Create a session based authentication guard.
     *
     * @param string $name
     * @param array $config
     * @return \Foundation\Auth\SessionGuard
     */
    protected function createSessionDriver($name, array $config)
    {
        $provider = new DatabaseUserProvider(
            $this->app['db'], $config['table']
        );

        return new SessionGuard(
            $name, $provider, $this->app['request'],
            $this->app['session'], $this->app['cookie']
        );
    }

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['config']['auth.defaults.guard'];
    }

    /**
     * Set the default authentication driver name.
     *
     * @param string $name
     * @return void
     */
    protected function setDefaultDriver($name)
    {
        $this->app['config']['auth.defaults.guard'] = $name;
    }

    /**
     * Get the guard configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["auth.guards.{$name}"];
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return $this->guard()->{$method}(...$params);
    }
}