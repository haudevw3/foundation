<?php

namespace Foundation\Application\Bootstrap;

use Exception;
use Foundation\Config\Config;
use Foundation\Contracts\Application\ApplicationContract;
use Foundation\Contracts\Config\ConfigContract;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param \Foundation\Contracts\Application\ApplicationContract $app
     * @return void
     */
    public function bootstrap(ApplicationContract $app)
    {
        $app->singleton('config', function () {
            return new Config;
        });

        $this->loadConfigurationFiles($app['config']);
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param \Foundation\Contracts\Config\ConfigContract $config
     * @return void
     * 
     * @throws \Exception
     */
    protected function loadConfigurationFiles(ConfigContract $config)
    {
        $array = [];

        foreach (scandir(config_path()) as $value) {
            if ($value == '.' || $value == '..') {
                continue;
            }

            $prefix = explode('.', $value)[0];

            $array[$prefix] = config_path($value);
        }

        foreach ($array as $key => $path) {
            if (file_exists($path)) {
                $config->set($key, require $path);
            } else {
                throw new Exception("Unable to load the [$path] configuration file.");
            }
        }
    }
}