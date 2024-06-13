<?php

namespace Foundation\Container;

use ArrayAccess;
use Closure;
use Foundation\Contracts\Container\BindingResolutionException;
use Foundation\Contracts\Container\ContainerContract;
use LogicException;
use ReflectionClass;
use TypeError;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * The current globally accessible instance of the container (if available).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The container's method bindings.
     *
     * @var \Closure[]
     */
    protected $methodBindings = [];

    /**
     * The registered type aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The registered aliases keyed by the abstract name.
     *
     * @var array
     */
    protected $abstractAliases = [];

    /**
     * The container's shared instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The container's scoped instances.
     *
     * @var array
     */
    protected $scopedInstances = [];

    /**
     * Contains temporary dependencies of the graph.
     *
     * @var array
     */
    protected $maps = [];

    /**
     * Check if the given abstract exists.
     *
     * @param string $abstract
     * @return bool
     */
    protected function has($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * Check if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
               $this->isAlias($abstract);
    }

    /**
     * Check if the given type is shared.
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared($abstract)
    {
        return (isset($this->bindings[$abstract]['shared']) &&
               $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Establish alias and place them into the respective array.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     * 
     * @throws \LogicException
     */
    public function alias($abstract, $alias)
    {
        if ($abstract === $alias) {
            throw new LogicException("Cannot alias [{$abstract}] to itself.");
        }

        $this->aliases[$alias] = $abstract;

        $this->abstractAliases[$abstract][] = $alias;
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    protected function isAlias($name)
    {
        return isset($this->aliases[$name]) ||
               in_array($name, $this->aliases);
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $name
     * @return string
     */
    protected function getAlias($name)
    {
        return isset($this->abstractAliases[$name])
                ? end($this->abstractAliases[$name])
                : $name;
    }

    /**
     * Register a binding with the container.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @param bool $shared
     * @return void
     * 
     * @throws \TypeError
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        // If the concrete is null, 
        // then the abstract must be the name of a class.
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If concrete is a string, it must be the name of a class.
        // and abstract must be the name of an alias. For example: 'name', interface.
        if (is_string($concrete)) {
            $this->bindingIf($abstract, $concrete, $shared);
        }

        // If concrete is a closure, it must return an object.
        if ($concrete instanceof Closure) {
            $this->instanceIf($abstract, $concrete, $shared);
        }
    }

    /**
     * Set a binding with the container.
     *
     * @param string $abstract
     * @param string $concrete
     * @param bool $shared
     * @return void
     */
    protected function bindingIf($abstract, $concrete, $shared)
    {
        if (! $this->isAlias($abstract)) {
            $this->bindings[$abstract] = compact('concrete', 'shared');
        }
    }

    /**
     * Set an instance with the container.
     * If $shared is true, it is registered as a singleton instance.
     * If $shared is false, it is registered as a scoped instance.
     *
     * @param string $abstract
     * @param \Closure $concrete
     * @param bool $shared
     * @return void
     */
    protected function instanceIf($abstract, $concrete, $shared)
    {
        $object = $concrete($this);

        if (! is_object($object)) {
            throw new TypeError(self::class.'::bind(): Argument #2 ($concrete) the returned Closure must be an Object.');
        }

        if ($shared) {
            if ($this->isAlias($abstract)) {
                $abstract = $this->getAlias($abstract);
            }

            $this->setInstance($abstract, $object);
        } else {
            $this->setScopedInstance($abstract, $concrete);
        }
    }

    /**
     * Set an object instance in the container for the given concrete type.
     *
     * @param string $concrete
     * @param object $object
     * @return void
     */
    protected function setInstance($concrete, $object)
    {
        $this->instances[$concrete] = $object;
    }

    /**
     * Set a closure as a scoped instance in the container for the given abstract type.
     *
     * @param string $abstract
     * @param \Closure $closure
     * @return void
     */
    protected function setScopedInstance($abstract, $closure)
    {
        $this->scopedInstances[$abstract] = $closure;
    }

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @param bool $shared
     * @return void
     */
    public function bindIf($abstract, $concrete = null, $shared = false)
    {
        if (! $this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register a shared binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @return void
     */
    public function singletonIf($abstract, $concrete = null)
    {
        if (! $this->bound($abstract)) {
            $this->singleton($abstract, $concrete);
        }
    }

    /**
     * The factory produces objects based on the given bindings.
     *
     * @return void
     */
    public function factory()
    {
        $maps = [];

        foreach ($this->getBindings() as $abstract => $value) {
            $maps[$abstract] = $value['concrete'];
        }

        $dependencies = $this->buildDependencyGraphs($maps);

        $this->resolveDependencies($dependencies);
    }

    /**
     * Build dependency graphs using the provided maps.
     *
     * @param array $maps
     * @return array
     */
    protected function buildDependencyGraphs($maps)
    {
        $dependencyGraphs = [];

        $dependencyGroups = [];

        $independencyGroups = [];

        // Here we are divided into two groups.
        // Groups without dependencies and groups with dependencies.
        // Groups with no dependencies will be resolved first.
        // The group has dependencies that will be resolved later.
        foreach ($maps as $abstract => $concrete) {
            $dependencies = $this->getConstructorDependencies($concrete);

            if (is_null($dependencies)) {
                $independencyGroups[] = $abstract;
            } else {
                $dependencyGroups[$abstract] = $dependencies;
            }
        }

        // Sort dependency groups with algorithm.
        $topologicalSort = new TopologicalSort($dependencyGroups);

        $dependencyGroups = $topologicalSort->sort();

        $dependencyGraphs = array_merge($independencyGroups, $dependencyGroups);

        $dependencyGraphs = array_unique($dependencyGraphs);

        return $dependencyGraphs;
    }

    /**
     * Resolve dependencies with the given dependency graphs.
     *
     * @param array $dependencies
     * @return void
     */
    protected function resolveDependencies($dependencies)
    {
        foreach ($dependencies as $dependency) {
            $concrete = $this->getConcrete($dependency);
            
            if ($this->has($dependency)) {
                continue;
            }

            if (is_null($this->getConstructorDependencies($concrete))) {
                $this->setInstance($dependency, new $concrete);
            } else {
                $arguments = [];

                $this->getConstructorDependencies($concrete, function ($object) use (&$arguments) {
                    $arguments[] = $object;
                });

                $this->setInstance($dependency, new $concrete(...$arguments));
            }
        }
    }

    /**
     * Get the dependencies with the given concrete.
     *
     * @param string $concrete
     * @param null|\Closure $callback
     * @return mixed
     */
    protected function getConstructorDependencies($concrete, $callback = null)
    {
        $reflector = new ReflectionClass($concrete);

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return null;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return null;
        }

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = Util::getParameterClassName($parameter);

            $dependencies[] = $name;

            // If the callback is a closure, this method retrieves
            // the dependencies associated with the given concrete.
            if ($callback instanceof Closure) {
                $callback($this->get($name));
            }
        }

        return $dependencies;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string $concrete
     * @return object
     */
    public function build($concrete)
    {
        // Dependencies related to the concrete must be bound beforehand.
        // Draw a map with dependencies.
        // Build the dependency map.
        // Return the object to be built.
        $this->bindIf($concrete);

        $this->drawMap($concrete);

        $maps = array_unique($this->maps);

        $this->maps = [];

        $dependencies = $this->buildDependencyGraphs($maps);

        $this->resolveDependencies($dependencies);

        return $this->get($concrete);
    }

    /**
     * Get all dependencies from parent to children.
     *
     * @param string $abstract
     * @param string $concrete
     * @return void
     */
    protected function drawMap($abstract, $concrete = null)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->maps[$abstract] = $concrete;

        $dependencies = $this->getConstructorDependencies($concrete);

        if (is_null($dependencies)) {
            return;
        }

        foreach ($dependencies as $dependency) {
            $abstract = $dependency;

            if ($this->bound($dependency)) {
                $dependency = $this->getConcrete($dependency);
            }

            $this->drawMap($abstract, $dependency);
        }
    }

    /**
     * Instantiate a abstract scoped instance of the given type.
     *
     * @param string $abstract
     * @return void
     * 
     * @throws \Foundation\Contracts\Container\BindingResolutionException
     */
    public function make($abstract)
    {
        if ($this->hasScoped($abstract)) {
            return $this->getScoped($abstract)();
        }

        throw new BindingResolutionException(self::class."::bind(): Not found for [$abstract].");
    }

    /**
     * Check if the given abstract exists within the scoped instances.
     *
     * @param string $abstract
     * @return bool
     */
    protected function hasScoped($abstract)
    {
        return isset($this->scopedInstances[$abstract]);
    }

    /**
     * Get the scoped instance with the given abstract.
     *
     * @param string $abstract
     * @return \Closure
     */
    protected function getScoped($abstract)
    {
        return $this->scopedInstances[$abstract];
    }

    /**
     * Call the given class method and inject its dependencies.
     *
     * @param string $concrete
     * @param string $method
     * @return mixed
     */
    public function call($concrete, $method)
    {
        return BoundMethod::call($this, $concrete, $method);
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Get the instance with the given name.
     *
     * @param string $name
     * @return object
     */
    public function get($name)
    {
        if ($this->isAlias($name)) {
            $name = $this->getAlias($name);
        }
        
        return $this->instances[$name];
    }

    /**
     * Get the container's bindings.
     *
     * @return array
     */
    protected function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the concrete with the given abstract.
     *
     * @param string $abstract
     * @return string
     */
    protected function getConcrete($abstract)
    {
        return $this->bindings[$abstract]['concrete'];
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }

    // /**
    //  * Dynamically access container services.
    //  *
    //  * @param string $key
    //  * @return mixed
    //  */
    // public function __get($key)
    // {
    //     return $this[$key];
    // }

    // /**
    //  * Dynamically set container services.
    //  *
    //  * @param string $key
    //  * @param mixed $value
    //  * @return void
    //  */
    // public function __set($key, $value)
    // {
    //     $this[$key] = $value;
    // }
}