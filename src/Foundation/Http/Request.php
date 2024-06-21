<?php

namespace Foundation\Http;

use Foundation\Contracts\Http\RequestContract;
use Foundation\Contracts\Routing\RedirectorContract;
use Foundation\Macroable\Traits\Macroable;
use Foundation\Contracts\Validation\ValidatorContract;

class Request implements RequestContract
{
    use Macroable;

    /**
     * Save the current request's route parameters if any.
     *
     * @var array
     */
    protected $params = [];

    /**
     * The validator implementation.
     *
     * @var \Foundation\Contracts\Validation\ValidatorContract
     */
    protected $validator;

    /**
     * The redirector implementation.
     *
     * @var Foundation\Contracts\Routing\RedirectorContract
     */
    protected $redirector;

    /**
     * Gets the input parameters from the current request.
     *
     * @return array
     */
    public function getParamFromRequest()
    {
        return $this->params;
    }

    /**
     * Get the parameters from the route and pass them to the request.
     *
     * @param array $params
     * @return void
     */
    public function setParamRouteForRequest($params = [])
    {
        $this->params = $params;
    }

    /**
     * Get the method from the current request.
     *
     * @return string
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if the request's HTTP method is a specific method.
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method)
    {
        return ($this->method() == strtoupper($method)) ? true : false;
    }

    /**
     * 
     * Get information about the path of the current request. No domain, no query string.
     *
     * @return string
     */
    public function path()
    {
        preg_match('~(\S+)[-?]~', $this->uri(), $matches);

        return isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : $matches[1];
    }

    /**
     * Get the uri from the current request.
     *
     * @return string
     */
    public function uri()
    {
        $uri = trim($_SERVER['REQUEST_URI'], '/');

        return preg_match('/\%/', $uri) ? preg_replace('/\?.*/', '', $uri) : $uri;
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function url()
    {
        return $this->domain().'/'.$this->uri();
    }

    /**
     * Get query string from current request.
     *
     * @return string
     */
    public function queryString()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    /**
     * 
     * Get the scheme and domain from the current request.
     *
     * @return string
     */
    public function domain()
    {
        return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
    }

    /**
     * Get path root on computer of client from the request.
     *
     * @return string
     */
    public function root()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Get ip of client from the request.
     *
     * @return string
     */
    public function ip()
    {
        $ip = null;

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if(isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if(isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'UNKNOWN';
        }
        
        return $ip;
    }

    /**
     * Filter input data and remove data if it feels dangerous.
     *
     * @param array $data
     * @param int $type
     * @param int $filter
     * @param int $options
     * @return array
     */
    public function filterInput($data, $type, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $options = 0)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $temp = filter_input($type, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            if ($temp) {
                $result[$key] = $temp;
            } else {
                $result[$key] = filter_input($type, $key, $filter, $options);
            }
        }

        return $result;
    }

    /**
     * Get all the request's input data.
     *
     * @return array
     */
    public function all()
    {
        $result = [];
        
        if (! empty($this->getParamFromRequest())) {
            return $this->getParamFromRequest();
        }

        if ($this->isMethod('GET') && ! empty($this->queryString())) {
            $result = $this->filterInput($_GET, INPUT_GET);
        }

        if ($this->isMethod('POST')) {
            $result = $this->filterInput($_POST, INPUT_POST);
        }

        return $result;
    }

    /**
     * Checks whether the given input key exists or not.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->all()[$key]);
    }

    /**
     * Checks if multiple given input keys exist or not.
     *
     * @param array $keys
     * @return bool
     */
    public function hasMany($keys)
    {
        foreach ($keys as $key) {
            if (! $this->has($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 
     * Check if the uploaded file exists with the given key.
     *
     * @param string $key
     * @return bool
     */
    public function hasFile($key)
    {
        return isset($_FILES[$key]);
    }

    /**
     * 
     * Gets an the specified keyword input value.
     *
     * @param string $key
     * @return mixed
     */
    public function input($key)
    {
        return $this->has($key) ? $this->all()[$key] : '';
    }

    /**
     * 
     * Gets input values ​​from specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function only($keys)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }

        return $result;
    }

    /**
     * Gets all input values ​​except specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function except($keys)
    {
        $result = $this->all();

        foreach ($keys as $key) {
            if (array_key_exists($key, $result)) {
                unset($result[$key]);
            }else {
                return [];
            }
        }

        return $result;
    }

    /**
     * Get the uploaded file with the given input key.
     *
     * @param string $key
     * @return mixed
     */
    public function file($key)
    {
        if (! $this->hasFile($key)) {
            return null;
        }

        $files = $_FILES[$key];
        $name = $files['name'];
        $type = $files['type'];
        $tmpName = $files['tmp_name'];
        $error = $files['error'];
        $size = $files['size'];

        if (is_array($name)) {
            $uploadedFiles = [];

            for ($i = 0; $i < count($name); $i++) {
                $uploadedFiles[] = $this->createUploadedFile(
                    $name[$i], $type[$i], $tmpName[$i], $error[$i], $size[$i]
                );
            }

            return $uploadedFiles;
        }

        return $this->createUploadedFile($name, $type, $tmpName, $error, $size);
    }

    /**
     * Create a new uploaded file instance.
     *
     * @param string $originalName
     * @param string $mimeType
     * @param string $realPath
     * @param int $error
     * @param int $size
     * @return void
     */
    protected function createUploadedFile($originalName, $mimeType, $realPath, $error, $size)
    {
        return new UploadedFile(
            $originalName, $mimeType, $realPath, $error, $size
        );
    }

    /**
     * Get the value of a parameter from the query string.
     *
     * @param string $key
     * @return string
     */
    public function query($key)
    {
        if ($this->isMethod('GET')) {
            preg_match("/$key=([\w]*)/", $this->queryString(), $matches);
        }

        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * Verify the input data to see if it complies with the given rules.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $default
     * @return \Foundation\Contracts\Validation\ValidatorContract
     */
    public function validate(array $data, array $rules, array $messages, array $default = null)
    {
        $validator = $this->validator->make($data, $rules, $messages);

        if ($validator->fails()) {
            return $this->redirector->back()->withInput($default)->withErrors();
        }

        return $validator;
    }

    /**
     * Set the validator implementation.
     *
     * @param \Foundation\Contracts\Validation\ValidatorContract $validator
     * @return void
     */
    public function setValidator(ValidatorContract $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Set the redirector implementation.
     *
     * @param Foundation\Contracts\Routing\RedirectorContract $redirector
     * @return void
     */
    public function setRedirector(RedirectorContract $redirector)
    {
        $this->redirector = $redirector;
    }
}