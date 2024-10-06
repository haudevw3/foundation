<?php

namespace Foundation\Routing;

use Closure;
use LogicException;

class RouteAction
{
    /**
     * Parse the given action into an array.
     *
     * @param  string          $uri
     * @param  array|\Closure  $action
     * @return array
     * 
     * @throws \LogicException
     */
    public static function parse($uri, $action)
    {
        // If the route instance no has action then we give exception with URI.
        if (is_null($action)) {
            throw new LogicException("Route for [$uri] has no action.");
        }

        // If the action is array then it should obligatory has two parameters.
        // First parameter is class name of controller.
        // Second parameter is method name of controller.
        // Property 'uses' will for we know that this function will call which type.
        elseif (is_array($action)) {
            return [
                'uses' => $action[0].'@'.$action[1],
                'controller' => ['name' => $action[0], 'method' => $action[1]],
            ];
        }

        // If the action is closure then we will specified that
        // it is '__invoke' of controller class.
        elseif ($action instanceof Closure) {
            return [
                'uses' => 'Controller@__invoke',
                'controller' => ['name' => 'Controller', 'method' => '__invoke'],
            ];
        }
    }
}