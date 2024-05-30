<?php

namespace Foundation\Container;

use Closure;
use Contracts\Container\BindingResolutionException;
use Contracts\Container\ContainerContract;
use ReflectionClass;
use TypeError;

class Container implements ContainerContract
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
    public $bindings = [];

    /**
     * The registered aliases keyed by the abstract name.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The container's shared instances
     *
     * @var array
     */
    public $instances = [];

    /**
     * The container's scoped instances.
     *
     * @var array
     */
    public $scopedInstances = [];

    /**
     * Contains temporary dependencies of the graph.
     *
     * @var array
     */
    protected $maps = [];

    /**
     * Check if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Check if the given concrete exists.
     *
     * @param string $concrete
     * @return bool
     */
    protected function has($concrete)
    {
        return isset($this->instances[$concrete]);
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
     * Check if the given abstract exists.
     *
     * @param string $abstract
     * @return bool
     */
    protected function hasAlias($abstract)
    {
        return isset($this->aliases[$abstract]);
    }

    /**
     * Check if the given abstract is alias.
     *
     * @param string $abstract
     * @return bool
     */
    protected function isAlias($abstract)
    {
        return (count(explode('\\', $abstract)) > 1) ? false : true;
    }

    /**
     * Get the alias with the given abstract.
     *
     * @param string $abstract
     * @return string
     */
    protected function getAlias($abstract)
    {
        return $this->aliases[$abstract];
    }

    /**
     * Set an alias with the given abstract.
     *
     * @param string $abstract
     * @param string $concrete
     * @return void
     */
    protected function setAlias($abstract, $concrete)
    {
        $this->aliases[$abstract] = $concrete;
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
     * Set a binding with the given abstract, concrete, shared flag.
     *
     * @param string $abstract
     * @param string $concrete
     * @param bool $shared
     * @return void
     */
    protected function binding($abstract, $concrete, $shared)
    {
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Set a binding with the given abstract, concrete, shared flag.
     * If the abstract is an alias.
     *
     * @param string $abstract
     * @param string $concrete
     * @param bool $shared
     * @return void
     */
    protected function bindingIf($abstract, $concrete, $shared)
    {
        if ($this->isAlias($abstract)) {
            $this->setAlias($abstract, $concrete);

            $abstract = $concrete;
        }

        $this->binding($abstract, $concrete, $shared);
    }

    /**
     * Set an instance with the given abstract, concrete, shared flag.
     * If shared is true, it is a singleton instance.
     * If shared is false, it is a scoped instance.
     *
     * @param string $abstract
     * @param \Closure $concrete
     * @param bool $shared
     * @return void
     */
    protected function instanceIf($abstract, $concrete, $shared)
    {
        $object = $concrete();

        if (! is_object($object)) {
            throw new TypeError(self::class.'::bind(): Argument #2 ($concrete) must be of type Object.');
        }

        if ($shared) {
            $concrete = get_class($object);

            $this->bindingIf($abstract, $concrete, $shared);

            $this->setInstance($concrete, $object);
        } else {
            $this->setScopedInstance($abstract, $concrete);
        }
    }

    /**
     * Set an object with the given concrete.
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
     * Set a closure with the given abstract.
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
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (is_string($concrete)) {
            $this->bindingIf($abstract, $concrete, $shared);
        }

        if ($concrete instanceof Closure) {
            $this->instanceIf($abstract, $concrete, $shared);
        }
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
     * Get the instance with the given abstract.
     *
     * @param string $abstract
     * @return object
     */
    public function get($abstract)
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        if ($this->bound($abstract)) {
            $abstract = $this->getConcrete($abstract);
        }

        return $this->instances[$abstract];
    }

    /**
     * Get the abstract with the given concrete.
     *
     * @param string $concrete
     * @return string|null
     */
    protected function getAbstract($concrete)
    {
        $abstract = array_keys($this->getBindings(), $concrete);

        return ! empty($abstract) ? reset($abstract) : null;
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
                $callback($this->get($this->getConcrete($name)));
            }
        }

        return $dependencies;
    }

    /**
     * The factory produces objects based on the given bindings.
     *
     * @return void
     */
    public function factory()
    {
        $maps = [];

        foreach ($this->getBindings() as $key => $value) {
            $maps[$key] = $value['concrete'];
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

            if ($this->has($concrete)) {
                continue;
            }

            $dependencies = $this->getConstructorDependencies($concrete);

            if (is_null($dependencies)) {
                $independencyGroups[] = $abstract;
            } else {
                $dependencyGroups[$abstract] = $dependencies;
            }
        }

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
            $dependency = $this->getConcrete($dependency);
            
            if ($this->has($dependency)) {
                continue;
            }

            if (is_null($this->getConstructorDependencies($dependency))) {
                $this->setInstance($dependency, new $dependency);
            } else {
                $arguments = [];

                $this->getConstructorDependencies($dependency, function ($object) use (&$arguments) {
                    $arguments[] = $object;
                });

                $this->setInstance($dependency, new $dependency(...$arguments));
            }
        }
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string $concrete
     * @return object
     */
    public function build($concrete)
    {
        $this->drawMap($concrete);

        $maps = array_unique($this->getMaps());

        $this->resetMaps();

        $dependencies = $this->buildDependencyGraphs($maps);

        $this->resolveDependencies($dependencies);

        return $this->get($concrete);
    }

    /**
     * Get all the maps.
     *
     * @return array
     */
    protected function getMaps()
    {
        return $this->maps;
    }

    /**
     * Set a map with the given abstract, concrete.
     *
     * @param string $abstract
     * @param string|null $concrete
     * @return void
     */
    protected function setMap($abstract, $concrete = null)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->maps[$abstract] = $concrete;
    }

    /**
     * Get all dependencies from parent to children.
     *
     * @param string $concrete
     * @return void
     */
    protected function drawMap($concrete)
    {
        if ($this->bound($concrete)) {
            $this->setMap($concrete, $this->getConcrete($concrete));
        } else {
            $this->setMap($concrete);

            $this->singletonIf($concrete);
        }

        $dependencies = $this->getConstructorDependencies($concrete);

        if (is_null($dependencies)) {
            return;
        }

        foreach ($dependencies as $dependency) {
            $this->drawMap($dependency);
        }
    }

    /**
     * Clear all dependencies currently stored in the maps.
     *
     * @return void
     */
    protected function resetMaps()
    {
        $this->maps = [];
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
     * Register a scoped binding in the container.
     *
     * @param string $abstract
     * @param string|null|\Closure $concrete
     * @return void
     */
    protected function scoped($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, false);
    }

    /**
     * Instantiate a abstract scoped instance of the given type.
     *
     * @param string $abstract
     * @return void
     * 
     * @throws \BindingResolutionException
     */
    public function make($abstract)
    {
        if ($this->hasScoped($abstract)) {
            return $this->getScoped($abstract)();
        }

        throw new BindingResolutionException(self::class."::bind(): Not found for [$abstract].");
    }

    /**
     * Call the given class method and inject its dependencies.
     *
     * @param string $concrete
     * @param string $method
     * @return mixed
     */
    public function callMethod($concrete, $method)
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
}