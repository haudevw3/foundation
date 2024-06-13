<?php

namespace Foundation\Validation;

use Foundation\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerValidator();
    }

    /**
     * Register the validator.
     *
     * @return void
     */
    protected function registerValidator()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Validator($app['db'], $app['session'], new ValidationRule, new ValidationData);

            $request = $app['request'];
            $request->setValidator($validator);
            $request->setRedirector($app['redirect']);

            return $validator;
        });
    }
}