<?php

use Foundation\Application\Application;

if (! function_exists('app')) {
    /**
     * Get the available application instance.
     *
     * @param string|null $abstract
     * @return mixed
     */
    function app($abstract = null)
    {
        $app = Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        return $app->get($abstract);
    }
}

if (! function_exists('auth')) {
    /**
     * Get the available auth instance.
     *
     * @param string|null $guard
     * @return \Foundation\Contracts\Auth\AuthenticatorContract|\Foundation\Contracts\Auth\GuardContract
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return app('auth');
        }

        return app('auth')->guard($guard);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->appPath($path);
    }
}

if (! function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @return string
     */
    function asset($path)
    {
        return config('app.url').trim($path, '/');
    }
}

if (! function_exists('back')) {
    /**
     * Create a new redirect response to the previous location.
     *
     * @param int $status
     * @param array $headers
     * @return \Foundation\Http\RedirectResponse
     */
    function back($status = 302, $headers = [])
    {
        return app('redirect')->back($status, $headers);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('config')) {
    /**
     * Get the configuration value with the given key. 
     * If there is no key passed in, the object will be returned.
     *
     * @param string|null $key
     * @param mixed $value
     * @return mixed
     */
    function config($key = null, $value = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (! is_null($value)) {
            return app('config')->set($key, $value);
        }

        return app('config')->get($key);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->configPath($path);
    }
}

if (! function_exists('cookie')) {
    /**
     * Set a cookie.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return void|\Foundation\Contracts\Cookie\CookieContract
     */
    function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null,
                            $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
    {
        if (is_null($name)) {
            return app('cookie');
        }

        return app('cookie')->set($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    function csrf_token()
    {
        $session = app('session');

        if (isset($session)) {
            return $session->token();
        }

        throw new RuntimeException('Application session not set.');
    }
}

if (! function_exists('error')) {
    /**
     * Retrieve an error input item.
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    function old($key, $default = 'errors')
    {
        return app('session')->consume($default, $key);
    }
}

if (! function_exists('old')) {
    /**
     * Retrieve an old input item.
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    function old($key, $default = 'olds')
    {
        return app('session')->consume($default, $key);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param string $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->publicPath($path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param string $path
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param string|null $to
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return \Foundation\Routing\Redirector|\Foundation\Http\RedirectResponse
     */
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (is_null($to)) {
            return app('redirect');
        }

        return app('redirect')->to($to, $status, $headers, $secure);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param array|string|null $key
     * @return \Foundation\Http\Request|string|array|null
     */
    function request($key = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        return app('request')->input($key);
    }
}

if (! function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param string $name
     * @param array|int|string $params
     * @return string
     */
    function route($name, $params = [])
    {
        $route = app('route')->getByName($name);

        $uri = $route['uri'];

        if (! empty($params)) {
            $uri = $route['replace'];
        }

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $uri = str_replace('?', ($key == 'page') ? "$key-$value" : $value, $uri);
            }
        } else {
            $uri = str_replace('?', $params, $uri);
        }

        return $uri;
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * @param string|null $key
     * @param mixed $value
     * @return mixed|\Foundation\Session\Session
     */
    function session($key = null, $value = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_null($value)) {
            return app('session')->get($key);
        }

        return app('session')->set($key, $value);
    }
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return \Foundation\Contracts\Validation\ValidatorContract
     */
    function validator(array $data = [], array $rules = [], array $messages = [])
    {
        if (func_num_args() === 0) {
            return app('validator');
        }

        return app('validator')->make($data, $rules, $messages);
    }
}

if (! function_exists('view')) {
    /**
     * Get contents for the given view.
     *
     * @param string $name
     * @param array $data
     * @param array $mergeData
     * @return mixed
     */
    function view($name = null, $data = [], $mergeData = [])
    {
        if (func_get_args() === 0) {
            return app('view');
        }

        return app('view')->make($name, $data, $mergeData);
    }
}

if(! function_exists('_namespace')) {
    /**
     * Get the namespace of the view.
     *
     * @param string $namespace
     * @return string
     */
    function _namespace($namespace)
    {
        return app('template')->_namespace($namespace);
    }
}

if (! function_exists('_include')) {
    /**
     * Include a file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    function _include($namespace, $data = null)
    {
        return app('template')->_include($namespace, $data);
    }
}

if (! function_exists('include_one')) {
    /**
     * Include a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    function include_one($namespace, $data = null)
    {
        return app('template')->include_one($namespace, $data);
    }
}

if (! function_exists('_require')) {
    /**
     * Require a file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    function _require($namespace, $data = null)
    {
        return app('template')->_require($namespace, $data);
    }
}

if (! function_exists('require_one')) {
    /**
     * Require a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    function require_one($namespace, $data = null)
    {
        return app('template')->require_one($namespace, $data);
    }
}

if (! function_exists('push')) {
    /**
     * Set the namespace of the view and starts output buffering.
     *
     * @param string $namespace
     * @return void
     */
    function push($namespace)
    {
        return app('template')->push($namespace);
    }
}

if (! function_exists('endpush')) {
    /**
     * End output buffering and retrieves the buffered output.
     *
     * @return void
     */
    function endpush()
    {
        return app('template')->endpush();
    }
}

if(! function_exists('render_js')) {
    /**
     * Render JavaScript content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    function render_js($namespace)
    {
        return app('template')->renderJs($namespace);
    }
}

if(! function_exists('render_css')) {
    /**
     * Render CSS content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    function render_css($namespace)
    {
        return app('template')->renderCss($namespace);
    }
}

if(! function_exists('render_component')) {
    /**
     * Render Component content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    function render_component($namespace)
    {
        return app('template')->renderComponent($namespace);
    }
}