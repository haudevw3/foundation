<?php

namespace Foundation\Support\Traits;

trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param callable|null $callback
     * @return $this|\Foundation\Support\TapChainingProxy
     */
    public function tap($callback = null)
    {
        return tap($this, $callback);
    }
}
