<?php

namespace Foundation\Routing;

class RouteWhere
{
    /**
     * The parameter names and the regular expression if has.
     *
     * @var array|string
     */
    protected $name;

    /**
     * The regular expression.
     *
     * @var string|null
     */
    protected $expression;

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres;

    /**
     * Create a new route Where instance.
     *
     * @param  array|string  $name
     * @param  string|null   $expression
     * @param  array         $wheres
     * @return void
     */
    public function __construct($name, $expression, $wheres)
    {
        $this->name = $name;
        $this->wheres = $wheres;
        $this->expression = $expression;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param  array|string  $name
     * @param  string|null   $expression
     * @return array
     */
    public static function parse($name, $expression)
    {
        $wheres = [];

        if (is_array($name)) {
            
            foreach ($name as $key => $value) {
                $wheres[$key] = $value;
            }
        } else {
            $wheres = [$name => is_null($expression)
                       ? '([^\/\?]+)' : $expression];
        }

        return new static($name, $expression, $wheres);
    }

    /**
     * The requirements of the symfony route.
     *
     * @return array
     */
    public function getRequirements()
    {
        return $this->wheres;
    }
}