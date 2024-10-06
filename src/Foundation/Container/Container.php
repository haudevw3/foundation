<?php

namespace Foundation\Container;

use ArrayAccess;
use Closure;
use Exception;
use Foundation\Container\Contracts\Container as ContainerContract;
use Foundation\Container\Events\Dispatcher;
use Foundation\Container\Exceptions\BindingResolutionException;
use Foundation\Container\Exceptions\CircularDependencyException;
use Foundation\Container\Exceptions\EntryNotFoundException;
use Foundation\Support\Arr;
use LogicException;
use ReflectionParameter;
use TypeError;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * The container instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * An array of the types that have been resolved.
     *
     * @var bool[]
     */
    protected $resolved = [];

    /**
     * The bindings registered in the container.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The method bindings are registered in the container.
     *
     * @var \Closure[]
     */
    protected $methodBindings = [];

    /**
     * The instances are shared in the container.
     *
     * @var object[]
     */
    protected $instances = [];

    /**
     * The instances are scoped in the container.
     *
     * @var array
     */
    protected $scopedInstances = [];

    /**
     * The aliases registered in the container.
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * The aliases are registered by the abstract name.
     *
     * @var array
     */
    protected $abstractAliases = [];

    /**
     * The extension closures for services.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * All of the registered tags.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var array
     */
    protected $parameterStack = [];

    /**
     * The contextual binding map.
     *
     * @var array
     */
    public $contextual = [];

    /**
     * All of the registered rebound callbacks.
     *
     * @var array
     */
    protected $reboundCallbacks = [];

    /**
     * The dispatcher instance.
     *
     * @var \Foundation\Container\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new the container instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->dispatcher = new Dispatcher;
    }

    /**
     * Define a contextual binding.
     *
     * @param  array|string  $concrete
     * @return \Foundation\Container\Contracts\ContextualBinder
     */
    public function when($concrete)
    {
        $aliases = [];

        foreach (Arr::wrap($concrete) as $name) {
            $aliases[] = $this->getAlias($name);
        }

        return new ContextualBinder($this, $aliases);
    }

    /**
     * Add an event to the dispatcher instance.
     *
     * @param  string|null  $abstract
     * @return \Foundation\Container\Contracts\Dispatcher
     */
    public function addEvent($abstract = null)
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return $this->dispatcher->add($abstract);
    }

    /**
     * Add a contextual binding to the container.
     *
     * @param  string           $concrete
     * @param  string           $abstract
     * @param  string|\Closure  $implementation
     * @return void
     */
    public function addContextualBinding($concrete, $abstract, $implementation)
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Establish aliases and place them into the respective array.
     *
     * @param  string  $abstract
     * @param  string  $alias
     * @return void
     * 
     * @throws \LogicException
     */
    public function alias($abstract, $alias)
    {
        if ($alias === $abstract) {
            throw new LogicException("[$abstract] is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;

        $this->abstractAliases[$abstract][] = $alias;
    }

    /**
     * Assign a set of tags to a given binding.
     *
     * @param  array|string  $abstracts
     * @param  array|mixed   ...$tags
     * @return void
     */
    public function tag($abstracts, $tags)
    {
        $tags = is_array($tags) ? $tags : array_slice(func_get_args(), 1);

        foreach ($tags as $tag) {
            if (! isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }

            foreach ((array) $abstracts as $abstract) {
                $this->tags[$tag][] = $abstract;
            }
        }
    }

    /**
     * Resolve all of the bindings for a given tag.
     *
     * @param  string    $tag
     * @return iterable
     */
    public function tagged($tag)
    {
        if (! isset($this->tags[$tag])) {
            return [];
        }

        return new RewindableGenerator(function () use ($tag) {
            foreach ($this->tags[$tag] as $abstract) {
                yield $this->make($abstract);
            }
        }, count($this->tags[$tag]));
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return $this->hasBinding($abstract) ||
               $this->hasInstance($abstract) ||
               $this->isAlias($abstract);
    }

    /**
     * Register a binding with the container.
     *
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @param  bool                  $shared
     * @return void
     * 
     * @throws \TypeError
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        // We'll always ensure that when re-binds something,
        // old instances aren't retained, which could lead to inconsistencies.
        $this->removeStaleInstances($abstract);

        // If concrete is null, then we'll always default
        // that the name abstract must be the name of a class.
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If concrete is a string, then it must be the name of a class
        // and the abstract name of it must be the name interface or alias.
        // If it not the name of a class, then sure that the exception
        // happen in process build of it.
        if (is_string($concrete)) {
            $concrete = $this->closure($abstract, $concrete);
        }

        if (! $concrete instanceof Closure) {
            throw new TypeError(self::class.'::bind(): Argument #2 [$concrete] must be of type Closure|string|null');
        }

        // Here, we'll binding the abstract name with the attribute of it.
        // This mean that we'll manage all the bindings.
        // It'll convenient to resolve problem by callbacks.
        $this->bindings[$abstract] = compact('concrete', 'shared');

        // If the abstract type was already resolved in this container we'll fire the
        // rebound listener so that any objects which have already gotten resolved
        // can have their copy of the object updated via the listener callbacks.
        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }

    /**
     * Get the closure to be used when building a type.
     *
     * @param  string  $abstract
     * @param  string  $concrete
     * @return \Closure
     */
    protected function closure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $this->build($concrete);
            }

            return $container->resolve(
                $concrete, $parameters, $raiseEvents = false
            );
        };
    }

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function resolved($abstract)
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return $this->isResolved($abstract) ||
               $this->hasInstance($abstract);
    }

    /**
     * Fire the rebound callbacks for the given abstract type.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function rebound($abstract)
    {
        $instance = $this->make($abstract);

        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * Get the rebound callbacks for a given type.
     *
     * @param  string  $abstract
     * @return array
     */
    protected function getReboundCallbacks($abstract)
    {
        return $this->reboundCallbacks[$abstract] ?? [];
    }

    /**
     * Bind a new callback to an abstract's rebind event.
     *
     * @param  string    $abstract
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rebinding($abstract, Closure $callback)
    {
        $this->reboundCallbacks[$abstract = $this->getAlias($abstract)][] = $callback;

        if ($this->bound($abstract)) {
            return $this->make($abstract);
        }
    }

    /**
     * Refresh an instance on the given target and method.
     *
     * @param  string  $abstract
     * @param  mixed   $target
     * @param  string  $method
     * @return mixed
     */
    public function refresh($abstract, $target, $method)
    {
        return $this->rebinding($abstract, function ($app, $instance) use ($target, $method) {
            $target->{$method}($instance);
        });
    }

    /**
     * Extend an abstract type in the container.
     *
     * @param  string    $abstract
     * @param  \Closure  $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend($abstract, Closure $closure)
    {
        $abstract = $this->getAlias($abstract);

        if ($this->hasInstance($abstract)) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);

            $this->rebound($abstract);
        } else {
            $this->extenders[$abstract][] = $closure;

            if ($this->resolved($abstract)) {
                $this->rebound($abstract);
            }
        }
    }

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @param  bool                  $shared
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
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register a shared binding if it hasn't already been registered.
     *
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @return void
     */
    public function singletonIf($abstract, $concrete = null)
    {
        if (! $this->bound($abstract)) {
            $this->singleton($abstract, $concrete);
        }
    }

    /**
     * Register a scoped binding in the container.
     *
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @return void
     */
    public function scoped($abstract, $concrete = null)
    {
        $this->scopedInstances[] = $abstract;

        $this->singleton($abstract, $concrete);
    }

    /**
     * Register a scoped binding if it hasn't already been registered.
     *
     * @param  string                $abstract
     * @param  string|null|\Closure  $concrete
     * @return void
     */
    public function scopedIf($abstract, $concrete = null)
    {
        if (! $this->bound($abstract)) {
            $this->scopedInstances[] = $abstract;

            $this->singleton($abstract, $concrete);
        }
    }

    /**
     * Get the binding with the given abstract.
     *
     * @param  string|callable  $abstract
     * @param  string           $key
     * @return mixed
     */
    public function binding($abstract, $key = null)
    {
        return $this->hasBinding($abstract)
                ? (is_null($key) ? $this->bindings[$abstract]
                : $this->bindings[$abstract][$key])
                : null;
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed   $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        $this->removeAbstractAlias($abstract);

        $isBound = $this->bound($abstract);

        unset($this->aliases[$abstract]);

        // We'll check to determine if this type has been bound before, and if it has
        // we will fire the rebound callbacks registered with the container and it
        // can be updated with consuming classes that have gotten resolved here.
        $this->instances[$abstract] = $instance;

        if ($isBound) {
            $this->rebound($abstract);
        }

        return $instance;
    }

    /**
     * Determine if the container has a method binding.
     *
     * @param  string  $method
     * @return bool
     */
    public function hasMethodBinding($method)
    {
        return isset($this->methodBindings[$method]);
    }

    /**
     * Bind a callback to resolve with Container::call.
     *
     * @param  array|string  $method
     * @param  \Closure      $callback
     * @return void
     */
    public function bindMethod($method, $callback)
    {
        $this->methodBindings[$this->parseBindMethod($method)] = $callback;
    }

    /**
     * Get the method to be bound in class@method format.
     *
     * @param  array|string  $method
     * @return string
     */
    protected function parseBindMethod($method)
    {
        if (is_array($method)) {
            return $method[0].'@'.$method[1];
        }

        return $method;
    }

    /**
     * Get the method binding for the given method.
     *
     * @param  string  $method
     * @param  mixed   $instance
     * @return mixed
     */
    public function callMethodBinding($method, $instance)
    {
        return call_user_func($this->methodBindings[$method], $instance, $this);
    }

    /**
     * Wrap the given closure such that its dependencies will be injected when executed.
     *
     * @param  \Closure  $callback
     * @param  array     $parameters
     * @return \Closure
     */
    public function wrap(Closure $callback, $parameters = [])
    {
        return function () use ($callback, $parameters) {
            return $this->call($callback, $parameters);
        };
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  string|callable       $callback
     * @param  array<string, mixed>  $parameters
     * @param  string|null           $defaultMethod
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call($callback, $parameters = [], $defaultMethod = null)
    {
        return BoundMethod::call($this, $callback, $parameters, $defaultMethod);
    }

    /**
     * Get a closure to resolve the given type from the container.
     *
     * @param  string    $abstract
     * @return \Closure
     */
    public function factory($abstract)
    {
        return function () use ($abstract) {
            return $this->make($abstract);
        };
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string|callable  $abstract
     * @param  array            $parameters
     * @return mixed
     *
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    public function make($abstract, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Resolve a concrete instance and trigger events for them if they have.
     *
     * @param  string|callable  $abstract
     * @param  array            $parameters
     * @param  bool             $raiseEvents
     * @return mixed
     */
    protected function resolve($abstract, $parameters = [], $raiseEvents = true)
    {
        $abstract = $this->getAlias($abstract);

        // First we'll fire any event handlers which handle the "before" resolving of
        // specific types. This gives some hooks the chance to add various extends
        // calls to change the resolution of objects that they're interested in.
        if ($raiseEvents) {
            $this->dispatcher->fireBeforeResolvingCallbacks($abstract, $parameters);
        }

        $concrete = $this->getContextualConcrete($abstract);

        $needsContextualBuild = ! empty($parameters) || ! is_null($concrete);

        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        if ($this->hasInstance($abstract) && ! $needsContextualBuild) {
            return $this->instances[$abstract];
        }

        $this->parameterStack[] = $parameters;

        if (is_null($concrete)) {
            $concrete = $this->getConcrete($abstract);
        }

        // We're ready to instantiate an instance of the concrete type registered for
        // the binding. This will instantiate the types, as well as resolve any of
        // its "nested" dependencies recursively until all have gotten resolved.
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        // If we defined any extenders for this type, we'll need to spin through them
        // and apply them to the object being built. This allows for the extension
        // of services, such as changing configuration or decorating the object.
        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        if ($this->isShared($abstract) && ! $needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        if ($raiseEvents) {
            $this->dispatcher->fireResolvingCallbacks($abstract, $object);
        }

        // Before returning, we will also set the resolved flag to "true" and pop off
        // the parameter overrides for this build. After those two things are done
        // we will be ready to return back the fully constructed class instance.
        $this->resolved[$abstract] = true;

        array_pop($this->parameterStack);

        return $object;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  string|\Closure  $concrete
     * @return mixed
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function build($concrete)
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        // We'll create a reflector instance to get parameters from it.
        $reflector = Reflector::newReflectionClass($concrete);

        // If the concrete is not instantiable, then we'll be sure that it'll be an in these situations after.
        // For example, an interface, abstract, trait, or __construct private.
        // We'll throw the exception here to for the user know.
        if (! $reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $this->buildStack[] = $concrete;

        $constructor = $reflector->getConstructor();

        // If the constructor is null then we are sure that no dependencies here.
        // Of course, we'll instantiate it right away.
        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $concrete;
        }

        // Get the parameters passed in the constructor.
        $dependencies = $constructor->getParameters();

        // Here, we'll loop through all dependencies and resolve them.
        // For each dependency, we'll determine if it meets all conditions of instantiate.
        // If any then we'll create it.
        // After all the dependencies are created then we'll create a new instance of this class,
        // injecting the created dependencies in.
        try {
            $arguments = $this->resolveDependencies($dependencies);
        } catch(BindingResolutionException $e) {
            array_pop($this->buildStack);

            throw $e;
        }

        array_pop($this->buildStack);
        
        return $reflector->newInstanceArgs($arguments);
    }

    /**
     * Resolve all of the dependencies from the concrete.
     *
     * @param  \ReflectionParameter  $dependencies
     * @return array
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function resolveDependencies($dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If the dependency has the parameter override.
            // We must replace it and continue running the commands.
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);

                continue;
            }

            // If the dependency is null. It means that it is a primitive variable or string.
            // We can not resolve this because not a class.
            // We'll try to resolve this with the different conditions.
            // If can't we'll must give an exception.
            $result = is_null(Reflector::getParameterClassName($dependency))
                        ? $this->resolvePrimitive($dependency)
                        : $this->resolveClass($dependency);
            
            if ($dependency->isVariadic()) {
                $results = array_merge($results, $result);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {   
        if (! is_null($concrete = $this->getContextualConcrete('$'.$parameter->getName()))) {
            return $concrete instanceof Closure ? $concrete($this) : $concrete;
        }
        
        // We'll determine if the parameter is a primitive value
        // and if it has the default value or not.
        // If any then we return the default value, if not then we can't resolve.
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
    }

    /**
     * Resolve a class-based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $parameter->isVariadic()
                        ? $this->resolveVariadicClass($parameter)
                        : $this->make(Reflector::getParameterClassName($parameter));
        }

        // If we can not resolve the class instance, we will check to see if the value
        // is optional, and if it is we will return the optional parameter value as
        // the value of the dependency, similarly to how we do this with scalars.
        catch (BindingResolutionException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                array_pop($this->parameterStack);

                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                array_pop($this->parameterStack);

                return [];
            }

            throw $e;
        }
    }

    /**
     * Resolve a class based variadic dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     */
    protected function resolveVariadicClass(ReflectionParameter $parameter)
    {
        $className = Reflector::getParameterClassName($parameter);

        $abstract = $this->getAlias($className);

        if (! is_array($concrete = $this->getContextualConcrete($abstract))) {
            return $this->make($className);
        }

        return array_map(function ($abstract) {
            return $this->resolve($abstract);
        }, $concrete);
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param  string|callable  $abstract
     * @return mixed
     */
    protected function getContextualConcrete($abstract)
    {
        // First, we'll determine if the abstract has a binding with the contextual.
        // If has then we'll must override it with the given contextual.
        if (! is_null($binding = $this->findInContextualBindings($abstract))) {
            return $binding;
        }

        // Second, we'll determine if the abstract is the alias.
        // If it's the alias abstract then we are sure that it is an instance of the application.
        // We'll spin through them to check if it must be the contextual binding.
        // If has we'll continue to override them with the given contextual.
        if (! is_null($aliases = $this->getAbstractAliases($abstract))) {
            foreach ($aliases as $alias) {
                if (! is_null($binding = $this->findInContextualBindings($alias))) {
                    return $binding;
                }
            }
        }
        
        return;
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @param  string|callable  $abstract
     * @return mixed
     */
    protected function findInContextualBindings($abstract)
    {
        return $this->contextual[end($this->buildStack)][$abstract] ?? null;
    }

    /**
     * Throw an exception that the concrete is not instantiable.
     *
     * @param  string  $concrete
     * @return void
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function notInstantiable($concrete)
    {
        if (! empty($this->buildStack)) {
            $previous = implode(', ', $this->buildStack);

            $message = "Target [$concrete] is not instantiable while building [$previous].";
        } else {
            $message = "Target [$concrete] is not instantiable.";
        }

        throw new BindingResolutionException($message);
    }

    /**
     * Throw an exception that an unresolvable with the primitive variable.
     *
     * @param  \ReflectionParameter  $parameter
     * @return void
     * 
     * @throws \Foundation\Container\Exceptions\BindingResolutionException
     */
    protected function unresolvablePrimitive(ReflectionParameter $parameter)
    {
        throw new BindingResolutionException(
            "Unresolvable dependency [$parameter] in class {$parameter->getDeclaringClass()->getName()}"
        );
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param  \ReflectionParameter  $dependency
     * @return bool
     */
    protected function hasParameterOverride($parameter)
    {
        return array_key_exists(
            $parameter->name, $this->getLastParameterOverride()
        );
    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param  \ReflectionParameter  $dependency
     * @return mixed
     */
    protected function getParameterOverride($parameter)
    {
        return $this->getLastParameterOverride()[$parameter->name];
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return empty($this->parameterStack) ? [] : end($this->parameterStack);
    }

    /**
     * Get the extender callbacks for a given type.
     *
     * @param  string  $abstract
     * @return array
     */
    protected function getExtenders($abstract)
    {
        return $this->extenders[$this->getAlias($abstract)] ?? [];
    }

    /**
     * Remove all of the extender callbacks for a given type.
     *
     * @param  string  $abstract
     * @return void
     */
    public function removeExtenders($abstract)
    {
        unset($this->extenders[$this->getAlias($abstract)]);
    }

    /**
     * Remove a resolved instance from the instance cache.
     *
     * @param  string  $abstract
     * @return void
     */
    public function removeInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Clear all of the instances from the container.
     *
     * @return void
     */
    public function removeInstances()
    {
        $this->instances = [];
    }

    /**
     * Clear all of the scoped instances from the container.
     *
     * @return void
     */
    public function removeScopedInstances()
    {
        foreach ($this->scopedInstances as $scoped) {
            unset($this->instances[$scoped]);
        }
    }

    /**
     * Remove all of the stale instances and aliases.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function removeStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    /**
     * Remove an alias from the contextual binding alias cache.
     *
     * @param  string  $searched
     * @return void
     */
    protected function removeAbstractAlias($searched)
    {
        if (! $this->isAlias($searched)) {
            return;
        }

        foreach ($this->abstractAliases as $abstract => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $searched) {
                    unset($this->abstractAliases[$abstract][$index]);
                }
            }
        }
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->aliases = [];
        $this->resolved = [];
        $this->bindings = [];
        $this->instances = [];
        $this->buildStack = [];
        $this->parameterStack = [];
        $this->abstractAliases = [];
        $this->scopedInstances = [];
        $this->reboundCallbacks = [];
        $this->dispatcher->flush();
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
     * Set the shared instance of the container.
     *
     * @param  \Illuminate\Contracts\Container\Container|null    $container
     * @return \Illuminate\Contracts\Container\Container|static
     */
    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Get the bindings of the container.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the concrete with the given abstract.
     *
     * @param  string|callable  $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        return $this->binding($abstract, 'concrete') ?? $abstract;
    }

    /**
     * Determine if the given abstract alias name exists.
     *
     * @param  string  $abstract
     * @return bool
     */
    protected function isAbstractAlias($abstract)
    {
        return isset($this->abstractAliases[$abstract]);
    }

    /**
     * Get the abstract aliases with the given abstract.
     *
     * @param  string      $abstract
     * @return array|null
     */
    protected function getAbstractAliases($abstract)
    {
       return $this->isAbstractAlias($abstract)
                ? $this->abstractAliases[$abstract]
                : null;
    }

    /**
     * Determine if the given alias name exists.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Get the alias with the given name.
     *
     * @param  string  $name
     * @return string
     */
    public function getAlias($name)
    {
        // The purpose called recursive is to return the desired result.
        // It must be the abstract alias.
        // The reason here is that we want it more flexible in any test cases.
        return $this->isAlias($name)
                ? $this->getAlias($this->aliases[$name])
                : $name;
    }

    /**
     * Determine if the given abstract exists inside the bindings.
     *
     * @param  string  $abstract
     * @return bool
     */
    protected function hasBinding($abstract)
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Determine if the given abstract exists inside the instances.
     *
     * @param  string  $abstract
     * @return bool
     */
    protected function hasInstance($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * Determine if the given abstract type is shared.
     *
     * @param  string  $abstract
     * @return bool
     */
    protected function isShared($abstract)
    {
        return ($this->binding($abstract, 'shared') === true);
    }

    /**
     * Determine if the given abstract type is resolved.
     *
     * @param  string  $abstract
     * @return bool
     */
    protected function isResolved($abstract)
    {
        return isset($this->resolved[$abstract]);
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param  mixed   $concrete
     * @param  string  $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->bound($id);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function get($id)
    {
        try {
            return $this->resolve($id);
        } catch (Exception $e) {
            if ($this->has($id) || $e instanceof CircularDependencyException) {
                throw $e;
            }

            throw new EntryNotFoundException($id, $e->getCode(), $e);
        }
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
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
     * @param  string  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed   $value
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
     * @param  string  $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->bindings[$key],
              $this->instances[$key],
              $this->resolved[$key]);
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}