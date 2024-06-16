<?php

namespace Foundation\View;

use Foundation\Contracts\View\TemplateContract;

class Template implements TemplateContract
{
    /**
     * Contains the paths to the css and js files.
     *
     * @var array
     */
    protected $stack = [];

    /**
     * The name of the view.
     *
     * @var string
     */
    protected $name;

    /**
     * The view instance.
     *
     * @var \Foundation\View\View
     */
    protected $view;

    /**
     * Create a new Template instance.
     *
     * @param \Foundation\View\View $view
     * @return void
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Set the name of the view and starts output buffering.
     *
     * @param string $name
     * @return void
     */
    public function push($name)
    {
        $this->name = $this->parseName($name);
        
        ob_start();
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
     * Parse the name of the view.
     *
     * @param string $name
     * @return string
     */
    protected function parseName($name)
    {
        $name = explode('.', trim($name));

        return implode('/', $name);
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
                    $this->name, 'css', $value
                );
            } else {
                $this->pushStack(
                    $this->name, 'js', $value
                );
            }
        }
    }

    /**
     * Push a new entry onto the stack.
     *
     * @param string $name
     * @param string $tag
     * @param mixed $value
     * @return void
     */
    protected function pushStack($name, $tag, $value)
    {
        $this->stack[$name][$tag][] = $value;
    }

    /**
     * Render JavaScript content associated with the specified name of the view.
     *
     * @param string $name
     * @return void
     */
    public function renderJs($name)
    {
        $name = $this->parseName($name);

        foreach ($this->getStack($name)['js'] as $link) {
            echo '<script src="'.$link.'"></script>';
        }
    }

    /**
     * Render CSS content associated with the specified name of the view.
     *
     * @param string $name
     * @return void
     */
    public function renderCss($name)
    {
        $name = $this->parseName($name);

        foreach ($this->getStack($name)['css'] as $link) {
            echo '<link rel="stylesheet" href="'.$link.'">';
        }
    }

    /**
     * Render Component content associated with the specified name of the view.
     *
     * @param array|string $components
     * @return void
     */
    public function renderComponent($components)
    {
        $components = is_array($components) ? $components : [$components];
        
        $paths = [];

        $this->view->parseView($components[0]);

        foreach ($components as $component) {
            $paths[] = $this->view->parseView($component)['path'];
        }

        foreach ($paths as $path) {
            require $path;
        }
    }

    /**
     * Get the stack associated with the specified key.
     *
     * @param string $key
     * @return array
     */
    protected function getStack($key)
    {
        return $this->stack[$key];
    }
}