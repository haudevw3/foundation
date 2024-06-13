<?php

namespace Foundation\Database\Eloquent;

use Foundation\Contracts\Database\EloquentBuilder;
use Foundation\Contracts\Database\ModelNotFoundException;
use Foundation\Contracts\Database\QueryBuilder;
use Foundation\Pagination\Paginator;
use Foundation\Support\Traits\ForwardsCalls;
use InvalidArgumentException;

class Builder implements EloquentBuilder
{
    use ForwardsCalls;

    /**
     * The base query builder instance.
     *
     * @var \Foundation\Contracts\Database\QueryBuilder
     */
    protected $query;

    /**
     * The model being queried.
     *
     * @var \Foundation\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The methods that should be returned from query builder.
     *
     * @var array
     */
    protected $passthrough = [
        'where',
        'andWhere',
        'orWhere',
        'groupBy',
        'having',
        'join',
        'leftJoin',
        'rightJoin',
        'take',
        'skip',
        'get',
        'first',
        'find',
        'count',
        'pluck',
        'max',
        'min',
        'avg',
        'sum'
    ];

    /**
     * Create a new Eloquent query builder instance.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @return void
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Foundation\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param \Foundation\Database\Eloquent\Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->table($model->getTable());

        return $this;
    }

    /**
     * Get all records from the database.
     *
     * @return array
     */
    public function all($columns = [])
    {
        return $this->query->get($columns);
    }

    /**
     * Find a record by primary key or raise an exception if not found.
     *
     * @param int $id
     * @param array $columns
     * @return array
     * 
     * @throws \ModelNotFoundException
     */
    public function findOrFail($id, $columns = [])
    {
        $result = $this->query->find(...func_get_args());

        if (is_null($result)) {
            $this->throwModelNotFoundException($id, $this->getModel());
        }

        return $result;
    }

    /**
     * Add a new record into the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function create($attributes = [])
    {
        return $this->query->insert($attributes);
    }

    /**
     * Update a record contained in the database based on id.
     *
     * @param int $id
     * @param array $attributes
     * @return bool
     */
    public function update($id, $attributes = [])
    {
        return $this->query->where('id', $id)->update($attributes);
    }

    /**
     * Delete a record present in the database based on id.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->query->delete($id);
    }

    /**
     * Paginate the given query.
     *
     * @param int|null $perPage
     * @param array $columns
     * @param array $options
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = [], $options = [])
    {
        $items = $this->all($columns);
        $perPage = $perPage ? $perPage : 10;

        if ($perPage > count($items)) {
            throw new InvalidArgumentException('The number of items per page cannot exceed the total number of items.');
        }

        $paginator = new Paginator(
            $items, $perPage, $options, app('request')
        );

        return $paginator->toArray();
    }

    /**
     * Exception for query model if error.
     *
     * @param int $id
     * @param \Foundation\Database\Eloquent\Model $model
     * @return void
     */
    protected function throwModelNotFoundException($id, $model)
    {
        throw new ModelNotFoundException("No query results for model [".get_class($model)."] with id [$id].");
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (in_array($method, $this->passthrough)) {
            return $this->forwardCallTo($this->query, $method, $params);
        }

        static::throwBadMethodCallException($method);
    }
}