<?php

namespace Foundation\Contracts\Application;

use Foundation\Contracts\Container\ContainerContract;

interface ApplicationContract extends ContainerContract
{
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version();

    /**
     * Get the path to the base directory.
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '');

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path
     * @return string
     */
    public function appPath($path = '');

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path
     * @return string
     */
    public function bootstrapPath($path = '');

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path
     * @return string
     */
    public function configPath($path = '');

    /**
     * Get the path to the public / web directory.
     *
     * @param string $path
     * @return string
     */
    public function publicPath($path = '');

    /**
     * Get the path to the storage directory.
     *
     * @param string $path
     * @return string
     */
    public function storagePath($path = '');

    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     * @return string
     */
    public function resourcePath($path = '');

    /**
     * Registers and bootstraps all necessary services for the application.
     *
     * @return void
     */
    public function registerAndBootstrapServices();
}