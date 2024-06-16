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
     * Include a file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function _include($namespace);

    /**
     * Include a single file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function include_one($namespace);

    /**
     * Require a file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function _require($namespace);

    /**
     * Require a single file based on the provided namespace.
     *
     * @param string $namespace
     * @return void
     */
    public function require_one($namespace);

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
     * @param array|string $namespaces
     * @return void
     */
    public function renderComponent($namespaces);
}