<?php

namespace Foundation\Application;

use Foundation\Application\Bootstrap\LoadConfiguration;
use Foundation\Application\Bootstrap\RegisterProviders;
use Foundation\Container\Container;
use Foundation\Contracts\Application\ApplicationContract;
use Foundation\Contracts\Http\KernelContract;
use Foundation\Http\Request;

class Application extends Container implements ApplicationContract, KernelContract
{
    /**
     * Current version.
     *
     * @var string
     */
    const VERSION = '1.0.8';

    /**
     * Application root directory path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Path to the application's app directory.
     *
     * @var string
     */
    protected $appPath;

    /**
     * Path to the application's bootstrap directory.
     *
     * @var string
     */
    protected $bootstrapPath;

    /**
     * Path to the application's configuration directory.
     *
     * @var string
     */
    protected $configPath;

    /**
     * Path to the application's public directory.
     *
     * @var string
     */
    protected $publicPath;

    /**
     * Path to the application's storage directory.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * Path to the application's resource directory.
     *
     * @var string
     */
    protected $resourcePath;

    /**
     * Path to the application's service provider directory if any.
     */

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get the path to the base directory.
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR. str_replace('/', DIRECTORY_SEPARATOR, $path) : $path);
    }

    /**
     * Set the base path for the application.
     *
     * @param string $basePath
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path
     * @return string
     */
    public function appPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app'.($path ? DIRECTORY_SEPARATOR. str_replace('/', DIRECTORY_SEPARATOR, $path) : $path);
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the public / web directory.
     *
     * @param string $path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'public'.($path ? DIRECTORY_SEPARATOR. str_replace('/', DIRECTORY_SEPARATOR, $path) : $path);
    }

    /**
     * Get the path to the storage directory.
     *
     * @param string $path
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'storage'.($path ? DIRECTORY_SEPARATOR. str_replace('/', DIRECTORY_SEPARATOR, $path) : $path);
    }

    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR. str_replace('/', DIRECTORY_SEPARATOR, $path) : $path);
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCoreContainerAliases();
        $this->registerAndBootstrapServices();
        $this->factory();
    }

    /**
     * Registers and bootstraps all necessary services for the application.
     *
     * @return void
     */
    public function registerAndBootstrapServices()
    {
       (new LoadConfiguration)->bootstrap($this);
       (new RegisterProviders)->bootstrap($this);
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param \Foundation\Http\Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        return $this[KernelContract::class]->handle($request);
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app' => [\Foundation\Application\Application::class, \Foundation\Container\Container::class, \Foundation\Contracts\Container\ContainerContract::class, \Foundation\Contracts\Application\ApplicationContract::class],
            'auth' => [\Foundation\Auth\Authenticator::class, \Foundation\Contracts\Auth\AuthenticatorContract::class],
            'config' => [\Foundation\Config\Config::class, \Foundation\Contracts\Config\ConfigContract::class],
            'cookie' => [\Foundation\Cookie\Cookie::class, \Foundation\Contracts\Cookie\CookieContract::class],
            'db' => [\Foundation\Database\Query\Builder::class, \Foundation\Contracts\Database\QueryBuilder::class],
            'db.connection' => [\Foundation\Database\Connection::class, \Foundation\Contracts\Database\ConnectionContract::class],
            'log' => [\Foundation\Log\Logger::class, \Foundation\Contracts\Log\LoggerContract::class],
            'redirect' => [\Foundation\Routing\Redirector::class, \Foundation\Contracts\Routing\RedirectorContract::class],
            'request' => [\Foundation\Http\Request::class, \Foundation\Contracts\Http\RequestContract::class],
            'response' => [\Foundation\Http\Response::class],
            'route' => [\Foundation\Routing\Route::class, \Foundation\Contracts\Routing\RouteContract::class],
            'session' => [\Foundation\Session\Session::class, \Foundation\Contracts\Session\SessionContract::class],
            'validator' => [\Foundation\Validation\Validator::class, \Foundation\Contracts\Validation\ValidatorContract::class],
            'view' => [\Foundation\View\View::class, \Foundation\Contracts\View\ViewContract::class],
            'template' => [\Foundation\View\Template::class, \Foundation\Contracts\View\TemplateContract::class]
            
        ] as $abstract => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($abstract, $alias);
            }
        }
    }
}