<?php

namespace Foundation\Routing;

class RouteCompiled
{
    /**
     * Compile the URI with the given parameters.
     * Used to replace the parameters within the URI.
     *
     * @param array $params
     * @param string $uri
     * @return string
     */
    public static function replace($params, $uri)
    {
        $compiled = $uri;
            
        foreach ($params as $key => $value) {
            $compiled = preg_replace('/\/\{('.$key.'\??)\}/', '/?', $compiled);
        }

        return "/$compiled";
    }

    /**
     * Compile template to search with request.
     *
     * @param array $params
     * @param string $uri
     * @return string
     */
    public static function pattern($params, $uri)
    {
        $explode = explode('/', $uri);

        $compiled = implode('\/', $explode);

        foreach ($params as $key => $value) {
            $compiled = preg_replace('/\{('.$key.')\}/', '([^\/]+)', $compiled);
        }

        return "/^$compiled$/";
    }
}