<?php

namespace Foundation\Routing;

class RegexGenerator
{
    /**
     * Build a regular expression based on the uri and parameter names of the route.
     *
     * @param  string  $uri
     * @param  array   $parameterNames
     * @param  bool    $isWhere
     * @return string
     */
    public static function pattern($uri, $parameterNames, $isWhere = false)
    {
        $segments = explode('/', $uri);

        $pattern = implode('\/', $segments);

        if (! empty($parameterNames)) {
            foreach ($parameterNames as $name => $regex) {
                if ($isWhere) {
                    $pattern = preg_replace('/\{('.$name.')\??\}/', $regex, $pattern);
                } else {
                    $name = $regex;

                    $pattern = preg_replace('/\{('.$name.')\??\}/', '([^\/\?]+)', $pattern);
                }
            }
        }

        return "/^$pattern(?:\?([^\/]*))?$/";
    }

    /**
     * Build a placeholder string based on the uri and parameter names of the route.
     *
     * @param  string  $uri
     * @param  array   $parameterNames
     * @return string|null
     */
    public static function placeholder($uri, $parameterNames)
    {
        if (empty($parameterNames)) {
            return null;
        }
        
        $pattern = $uri;
            
        foreach ($parameterNames as $name) {
            $pattern = preg_replace('/\{('.$name.'\??)\}/', '?', $pattern);
        }

        return $pattern;
    }
}