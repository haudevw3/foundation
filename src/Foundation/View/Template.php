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
        $this->namespace = $namespace;
        
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

        preg_match_all('~(\S+?\.css)|(\S+?\.js)|(\S+?\.php)~', $output, $matches);

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
     * Get the namespace of the view.
     *
     * @param string $namespace
     * @return string
     */
    public function _namespace($namespace)
    {
        return $this->parseNamespace($namespace)['path'];
    }

    /**
     * Render JavaScript content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderJs($namespace)
    {
        if (isset($this->getStack($namespace)['js'])) {
            foreach ($this->getStack($namespace)['js'] as $value) {
                echo '<script src="'.$value.'"></script>';
            }
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
        if (isset($this->getStack($namespace)['css'])) {
            foreach ($this->getStack($namespace)['css'] as $value) {
                echo '<link rel="stylesheet" href="'.$value.'">';
            }
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
     * @param mixed $name
     * @return void
     */
    public function _include($namespace, $data = null)
    {
        if (! is_null($data)) {
            extract($data);
        }

        include $this->_namespace($namespace);
    }

    /**
     * Include a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function include_one($namespace, $data = null)
    {
        if (! is_null($data)) {
            extract($data);
        }

        include_once $this->_namespace($namespace);
    }

    /**
     * Require a file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function _require($namespace, $data = null)
    {
        if (! is_null($data)) {
            extract($data);
        }

        require $this->_namespace($namespace);
    }

    /**
     * Require a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function require_one($namespace, $data = null)
    {
        if (! is_null($data)) {
            extract($data);
        }

        require_once $this->_namespace($namespace);
    }
}