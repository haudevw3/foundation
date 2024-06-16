<?php

namespace Foundation\View;

trait ViewHelpers
{
    /**
     * Parse the namespace from the given view.
     *
     * @param string $namespace
     * @return array
     */
    public function parseNamespace($namespace)
    {
        $explode = explode('.', $namespace);

        // Assume the first position in the array is a key to identify whether a view exists in the configuration item.
        // Check if the view configuration contains the given key value.
        // If it exists, this is key registered via the service provider.
        // If it does not exist, it will be the default of the application.
        $temp = $explode[0];

        $prefix = isset(config('view.paths')[$temp]) ? $temp : 'default';

        $name = end($explode);

        unset($explode[0]);
        array_pop($explode);

        $path = null;

        if ($prefix == 'default') {
            $path = config('view.paths')[$prefix].
                    (($temp != $name) ? DIRECTORY_SEPARATOR.$temp : '');
        } else {
            $path = str_replace(
                '/', DIRECTORY_SEPARATOR, config('view.paths')[$prefix]
            );

            $path = base_path($path);
        }

        $path = $path.DIRECTORY_SEPARATOR.
                implode(DIRECTORY_SEPARATOR, $explode).$name.'.php';

        return [
            'prefix' => $prefix,
            'name' => $name,
            'path' => $path
        ];
    }
}