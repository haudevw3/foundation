<?php

namespace Foundation\View;

use Foundation\Contracts\View\ViewContract;
use Foundation\Contracts\View\ViewNotFoundException;

class View implements ViewContract
{
    /**
     * The name of the view.
     *
     * @var string
     */
    protected $name;

    /**
     * The namespace associated with the view.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The path to the view file.
     *
     * @var string
     */
    protected $path;

    /**
     * The array of view data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Get the view contents.
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return void
     * 
     * @throws \Foundation\Contracts\View\ViewNotFoundException
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $array = $this->parseView($view);

        $name = $array['name'];
        $namespace = $array['namespace'];
        $path = $array['path'];

        if (! file_exists($path)) {
            throw new ViewNotFoundException("View [$name] does not exits.");
        }

        $data = array_merge($data, $mergeData);

        $this->setName($name);
        $this->setNamespace($namespace);
        $this->setPath($path);
        $this->setData($data);

        extract($data);
        
        require $path;
    }

    /**
     * Determine if a given view exists.
     *
     * @param string $view
     * @return bool
     */
    public function exists($view)
    {
        $array = $this->parseView($view);
        
        return file_exists($array['path']) ? true : false;
    }

    /**
     * Parse the view path and namespace from the given view.
     *
     * @param string $view
     * @return array
     */
    public function parseView($view)
    {
        $explode = explode('.', $view);

        // Assume the namespace is the first position in the array.
        // Check if the view configuration contains the namespace entry.
        // If it exists, this is the namespace registered via the service provider.
        // If it does not exist, it will be the default.
        $prefix = $explode[0];

        $namespace = isset(config('view.paths')[$prefix])
                     ? $prefix : 'default';

        $name = end($explode);

        unset($explode[0]);
        array_pop($explode);

        $path = null;

        if ($namespace == 'default') {
            $path = config('view.paths')[$namespace].
                    (($prefix != $name) ? DIRECTORY_SEPARATOR.$prefix : '');
        } else {
            $path = str_replace(
                '/', DIRECTORY_SEPARATOR, config('view.paths')[$namespace]
            );

            $path = base_path($path);
        }

        $path = $path.DIRECTORY_SEPARATOR.
                implode(DIRECTORY_SEPARATOR, $explode).$name.'.php';

        return [
            'name' => $name,
            'namespace' => $namespace,
            'path' => $path
        ];
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    protected function name()
    {
        return $this->name;
    }

    /**
     * Set the name of the view.
     *
     * @param string $name
     * @return void
     */
    protected function setName($name)
    {
        return $this->name = $name;
    }

    /**
     * Get the namespace associated with the view.
     *
     * @return string
     */
    protected function namespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace associated with the view.
     *
     * @param string $namespace
     * @return void
     */
    protected function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Get the path to the view file.
     *
     * @return string
     */
    protected function path()
    {
        return $this->path;
    }

     /**
     * Set the path to the view.
     *
     * @param string $path
     * @return void
     */
    protected function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the array of view data.
     *
     * @return array
     */
    protected function data()
    {
        return $this->data;
    }

    /**
     * Set the array of view data.
     *
     * @param array $data
     * @return void
     */
    protected function setData($data)
    {
        $this->data = $data;
    }
}