<?php

namespace Foundation\Routing;

class RouteUri
{
    /**
     * Parse parameters to compile a URI.
     *
     * @param string $uri
     * @return array|null
     */
    public static function parseParams($uri)
    {
        preg_match_all('/\{(\w+?)\??\}/', $uri, $matches);

        $params = null;

        if (! empty($matches[1])) {
            $params = array_fill_keys($matches[1], null);
        }

        return $params;
    }
}