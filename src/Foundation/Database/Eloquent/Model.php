<?php

namespace Foundation\Database\Eloquent;

abstract class Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the table associated with the model.
     *
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get a new query builder.
     *
     * @return \Foundation\Contracts\Database\QueryBuilder
     */
    public function newQuery()
    {
        return app('db')->table($this->table);
    }

    /**
     * Get a new eloquent builder.
     *
     * @return \Foundation\Database\Eloquent\Builder
     */
    public function newModelQuery()
    {
        return $this->newEloquentBuilder(app('db'))->setModel($this);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @return \Foundation\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return $this->newModelQuery()->$method(...$params);
    }
    
    /**
     * Handle dynamic static method calls into the model.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return (new static)->newModelQuery()->$method(...$params);
    }
}