<?php

namespace Foundation\Contracts\View;

interface ViewContract
{
    /**
     * Determine if a given view exists.
     *
     * @param string $view
     * @return bool
     */
    public function exists($view);

    /**
     * Get the view contents.
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return void
     */
    public function make($view, $data = [], $mergeData = []);
}