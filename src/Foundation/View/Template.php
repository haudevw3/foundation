<?php

namespace Foundation\View;

use Foundation\Contracts\View\TemplateContract;

class Template implements TemplateContract
{
    use ViewHelpers;

    /**
     * Contains the paths to the css and js files.
     *
     * @var array
     */
    protected $stack = [];

    /**
     * The namespace of the view.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Set the namespace of the view and starts output buffering.
     *
     * @param string $namespace
     * @return void
     */
    public function push($namespace)
    {
        $this->namespace = $this->convertNamespace($namespace);
        
        ob_start();
    }

    /**
     * Convert the namespace of the view.
     *
     * @param string $namespace
     * @return string
     */
    protected function convertNamespace($namespace)
    {
        $namespace = explode('.', trim($namespace));

        return implode('/', $namespace);
    }

    /**
     * End output buffering and retrieves the buffered output.
     *
     * @return void
     */
    public function endpush()
    {
        $output = ob_get_clean();

        preg_match_all('~(\S+?\.css)|(\S+?\.js)~', $output, $matches);

        $this->addStacks($matches[0]);
    }

    /**
     * Add the provided matches to the stack.
     *
     * @param array $matches
     * @return void
     */
    protected function addStacks($matches)
    {
        foreach ($matches as $value) {
            if (preg_match('~css~', $value)) {
                $this->pushStack(
                    $this->namespace(), 'css', $value
                );
            } else {
                $this->pushStack(
                    $this->namespace(), 'js', $value
                );
            }
        }
    }

    /**
     * Push a new entry onto the stack.
     *
     * @param string $namespace
     * @param string $tag
     * @param string $value
     * @return void
     */
    protected function pushStack($namespace, $tag, $value)
    {
        $this->stack[$namespace][$tag][] = $value;
    }

    /**
     * Get the namespace.
     *
     * @return string
     */
    protected function namespace()
    {
        return $this->namespace;
    }

    /**
     * Render JavaScript content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderJs($namespace)
    {
        $namespace = $this->convertNamespace($namespace);

        foreach ($this->getStack($namespace)['js'] as $value) {
            echo '<script src="'.$value.'"></script>';
        }
    }

    /**
     * Render CSS content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderCss($namespace)
    {
        $namespace = $this->convertNamespace($namespace);

        foreach ($this->getStack($namespace)['css'] as $value) {
            echo '<link rel="stylesheet" href="'.$value.'">';
        }
    }

    /**
     * Render Component content associated with name of the view.
     *
     * @param array|string $namespaces
     * @return void
     */
    public function renderComponent($namespaces)
    {
        $namespaces = is_array($namespaces) ? $namespaces : [$namespaces];

        foreach ($namespaces as $namespace) {
            $this->_require($namespace);
        }
    }

    /**
     * Get the stack with key the given.
     *
     * @param string $key
     * @return array
     */
    protected function getStack($key)
    {
        return $this->stack[$key];
    }

    /**
     * Include a file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function _include($namespace)
    {
        $path = $this->parseNamespace($namespace)['path'];

        include $path;
    }

    /**
     * Include a single file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function include_one($namespace)
    {
        $path = $this->parseNamespace($namespace)['path'];

        include_once $path;
    }

    /**
     * Require a file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function _require($namespace)
    {
        $path = $this->parseNamespace($namespace)['path'];

        require $path;
    }

    /**
     * Require a single file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function require_one($namespace)
    {
        $path = $this->parseNamespace($namespace)['path'];

        require_once $path;
    }
}