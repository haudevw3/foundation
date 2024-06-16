<?php

namespace Foundation\Contracts\View;

interface TemplateContract
{
    /**
     * Set the name of the view and starts output buffering.
     *
     * @param string $name
     * @return void
     */
    public function push($name);

    /**
     * End output buffering and retrieves the buffered output.
     *
     * @return void
     */
    public function endpush();

    /**
     * Render JavaScript content associated with the specified name of the view.
     *
     * @param string $name
     * @return void
     */
    public function renderJs($name);

    /**
     * Render CSS content associated with the specified name of the view.
     *
     * @param string $name
     * @return void
     */
    public function renderCss($name);

    /**
     * Render Component content associated with the specified name of the view.
     *
     * @param array|string $components
     * @return void
     */
    public function renderComponent($components);
}