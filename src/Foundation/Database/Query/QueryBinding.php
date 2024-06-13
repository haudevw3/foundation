<?php

namespace Foundation\Database\Query;

trait QueryBinding
{
    /**
     * The current query value bindings.
     *
     * @var array
     */
    protected $bindings = [
        'select'    => 'SELECT * FROM',
        'table'     => '',
        'where'     => '',
        'andWhere'  => '',
        'orWhere'   => '',
        'orderBy'   => '',
        'groupBy'   => '',
        'having'    => '',
        'join'      => '',
        'leftJoin'  => '',
        'rightJoin' => '',
        'limit'     => '',
        'offset'    => ''
    ];

    /**
     * Retrieve all constraints for the current query.
     *
     * @return array
     */
    protected function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Retrieves a value bound to the given key.
     *
     * @param string $key
     * @return string
     */
    protected function getBinding($key)
    {
        return $this->bindings[$key];
    }

    /**
     * Check if a key has been bound.
     *
     * @param string $key
     * @return bool
     */
    protected function bound($key)
    {
        return empty($this->bindings[$key]);
    }

    /**
     * Binds a query to the given key and value.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function bind($key, $value)
    {
        $this->bindings[$key] = $value;
    }

    /**
     * Binds a query to the given key and value.
     * If that key already has a value, continue concatenating the string.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function bindIf($key, $value)
    {
        if ($this->bound($key)) {
            $this->bind($key, $value);
        } else {
            $this->bind($key, $this->getBinding($key)." $value");
        }
    }

    /**
     * Reset all constraints to original.
     *
     * @return void
     */
    protected function resetBindings()
    {
        foreach ($this->getBindings() as $key => $value) {
            $this->bind($key, '');
        }

        $this->bind('select', 'SELECT * FROM');
    }

    /**
     * Constraints are prepared as query strings.
     *
     * @return string
     */
    protected function preparedBindings()
    {
        return implode(' ', $this->getBindings());
    }
}