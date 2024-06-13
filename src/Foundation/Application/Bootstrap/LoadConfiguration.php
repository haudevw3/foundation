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
        foreach ([
            'app' => config_path('app.php'),
            'auth' => config_path('auth.php'),
            'connection' => config_path('database.php'),
            'cookie' => config_path('cookie.php'),
            'view' => config_path('view.php')
        
            ] as $key => $path) {
                if (file_exists($path)) {
                    $config->set($key, require $path);
                } else {
                    throw new Exception("Unable to load the [$path] configuration file.");
                }
        }
    }
}