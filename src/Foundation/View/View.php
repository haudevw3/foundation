<?php

namespace Foundation\View;

use Foundation\Contracts\View\ViewContract;
use Foundation\Contracts\View\ViewNotFoundException;

class View implements ViewContract
{
    use ViewHelpers;

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
        $array = $this->parseNamespace($view);

        $name = $array['name'];
        $path = $array['path'];

        if (! file_exists($path)) {
            throw new ViewNotFoundException("View [$name] does not exits.");
        }

        $data = array_merge($data, $mergeData);

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
        $array = $this->parseNamespace($view);
        
        return file_exists($array['path']) ? true : false;
    }
}