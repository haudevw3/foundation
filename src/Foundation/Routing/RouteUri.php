<?php

namespace Foundation\Routing;

class RouteUri
{
    /**
     * The URI of the route.
     *
     * @var string
     */
    protected $uri;

    /**
     * The parameter names of the route.
     *
     * @var array
     */
    protected $parameterNames;

    /**
     * Create a new route URI instance.
     *
     * @param  string       $uri
     * @param  array|null   $parameterNames
     * @return void
     */
    public function __construct($uri, $parameterNames)
    {
        $this->uri = $uri;
        $this->parameterNames = $parameterNames;
    }

    /**
     * Parse the given URI.
     *
     * @param  string  $uri
     * @return $this
     */
    public static function parse($uri)
    {
        return new static(
            preg_replace('/\{(\w+?)\?\}/', '{$1}', $uri),
            static::parseParameterNames($uri) ?? [],
        );
    }

    /**
     * The URI parse to get parameter names.
     *
     * @param  string  $uri
     * @return array|null
     */
    protected static function parseParameterNames($uri)
    {
        preg_match_all('/\{(\w+?)\??\}/', $uri, $matches);

        $parameterNames = empty($matches[1]) ? null : $matches[1];

        return $parameterNames;
    }

    /**
     * The path of the symfony route.
     *
     * @return string
     */
    public function path()
    {
        return $this->uri;
    }

    /**
     * The optional parameters of the symfony route.
     *
     * @return array
     */
    public function getOptionalParameters()
    {
        return $this->parameterNames;
    }
}