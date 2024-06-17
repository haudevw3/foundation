<?php

namespace Foundation\Contracts\View;

interface TemplateContract
{
    /**
     * Set the namespace of the view and starts output buffering.
     *
     * @param string $namespace
     * @return void
     */
    public function push($namespace);

    /**
     * End output buffering and retrieves the buffered output.
     *
     * @return void
     */
    public function endpush();

    /**
     * Get the namespace of the view.
     *
     * @param string $namespace
     * @return string
     */
    public function _namespace($namespace);

    /**
     * Include a file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $name
     * @return void
     */
    public function _include($namespace, $data = null);

    /**
     * Include a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function include_one($namespace, $data = null);

    /**
     * Require a file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function _require($namespace, $data = null);

    /**
     * Require a single file based on the provided namespace.
     *
     * @param string $namespace
     * @param mixed $data
     * @return void
     */
    public function require_one($namespace, $data = null);

    /**
     * Render JavaScript content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderJs($namespace);

    /**
     * Render CSS content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderCss($namespace);

    /**
     * Render Component content associated with name of the view.
     *
     * @param string $namespace
     * @return void
     */
    public function renderComponent($namespace);
}