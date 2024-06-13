<?php

namespace Foundation\Contracts\Http;

use Foundation\Http\Request;

interface KernelContract
{
    /**
     * Handle an incoming HTTP request.
     *
     * @param \Foundation\Http\Request $request
     * @return mixed
     */
    public function handle(Request $request);
}