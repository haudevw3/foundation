<?php

namespace Foundation\Database\Query;

use Foundation\Contracts\Database\ConnectionContract;
use Foundation\Contracts\Database\QueryBuilder;
use Foundation\Macroable\Traits\Macroable;
use InvalidArgumentException;

class Builder implements QueryBuilder
{
    use QueryBinding, Macroable {
        __call as macroCall;
    }

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    protected $columns;

    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '^', '<<', '>>', '&~',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*'
    ];

    /**
     * All of the available bitwise operators.
     *
     * @var string[]
     */
    protected $bitwiseOperators = [
        '&', '|', '^', '<<', '>>', '&~'
    ];

    /**
     * Get the PDO connection instance.
     *
     * @var \Foundation\Contracts\Database\ConnectionContract
     */
    protected $connection;

    /**
     * Create a new query builder instance.
     *
     * @param \Foundation\Contracts\Database\ConnectionContract $connection
     * @return void
     */
    public function __construct(ConnectionContract $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Select columns to query.
     *
     * @param array $columns
     * @return $this
     */
    public function select($columns = [])
    {
        if (is_array($columns) && count($columns) > 0) {
            $columns = implode(',', $columns);

            $this->bind('select', "SELECT $columns FROM");
        }

        return $this;
    }

    /**
     * Table to be queried.
     *
     * @param string $table
     * @return $this
     */
    public function table($table)
    {
        $this->resetBindings();

        $this->bind('table', $table);

        return $this;
    }

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null)
    {
        $result = $this->parseOperatorAndValue($operator, $value);

        $operator = $result[0];
        $value    = $result[1];

        $this->bind('where', "WHERE $column $operator '$value'");

        return $this;
    }

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function andWhere($column, $operator = null, $value = null)
    {
        $result = $this->parseOperatorAndValue($operator, $value);

        $operator = $result[0];
        $value    = $result[1];

        $this->bindIf('andWhere', "AND $column $operator '$value'");

        return $this;
    }

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        $result = $this->parseOperatorAndValue($operator, $value);

        $operator = $result[0];
        $value    = $result[1];

        $this->bindIf('orWhere', "OR $column $operator '$value'");

        return $this;
    }

    /**
     * Analyze the given operator and whether the value combination is legal or not.
     *
     * @param mixed $operator
     * @param mixed $value
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function parseOperatorAndValue($operator, $value)
    {
        if ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        if (! is_null($value)) {
            if ($this->invalidOperator($operator)) {
                throw new InvalidArgumentException("The input operator [$operator] is not valid.");
            }
            
            return [$operator, $value];
        }

        return ['=', $value = $operator];
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param mixed $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), $this->operators, true);
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators);
    }

    /**
     * Sort results by a specific column.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->bind('orderBy', "ORDER BY $column $direction");

        return $this;
    }

    /**
     * Group the results by one or more columns.
     *
     * @param array|string $groups
     * @return $this
     */
    public function groupBy(...$groups)
    {

    }

    /**
     * Condition to filter grouped results.
     *
     * @param string $column
     * @param string|null $operator
     * @param mixed $value
     * @return $this
     */
    public function having($column, $operator = null, $value = null)
    {

    }

    /**
     * Add table to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return void
     */
    public function join($table, $first, $operator = null, $second = null)
    {

    }

    /**
     * Add a LEFT JOIN to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {

    }

    /**
     * Add a RIGHT JOIN to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {

    }

    /**
     * Limit the number of records returned.
     *
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        $this->bind('limit', "LIMIT $value");

        return $this;
    }

    /**
     * Skip a number of records from the beginning of the results.
     *
     * @param int $value
     * @return $this
     */
    public function offset($value)
    {
        $this->bind('offset', "OFFSET $value");

        return $this;
    }

    /**
     * Limit the number of records returned.
     *
     * @param int $value
     * @return $this
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Skip a number of records from the beginning of the results.
     *
     * @param int $value
     * @return $this
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Get all results of the query.
     *
     * @param array $columns
     * @return array|null
     */
    public function get($columns = [])
    {
        if (! empty($columns)) {
            $this->select($columns);
        }

        return $this->connection->fetchAll($this->preparedBindings());
    }

    /**
     * Get the first record of the query.
     *
     * @param array $columns
     * @return array|null
     */
    public function first($columns = [])
    {
        if (! empty($columns)) {
            $this->select($columns);
        }

        return $this->connection->fetch($this->preparedBindings());
    }

    /**
     * Find a record by the primary key.
     *
     * @param int $id
     * @param array $columns
     * @return array|null
     */
    public function find($id, $columns = [])
    {
        return $this->where('id', $id)->first($columns);
    }

    /**
     * 
     * Count the number of records that satisfy the condition, if any.
     *
     * @param string $column
     * @param string $as
     * @return int
     */
    public function count($column = '*', $as = 'count')
    {
        $this->bind('select', "SELECT COUNT($column) AS $as FROM");

        return (int) $this->get()[0][$as];
    }

    /**
     * Get an array of values from a specific column.
     *
     * @param string $column
     * @param string|null $key
     * @return array|null
     */
    public function pluck($column, $key = null)
    {
        if (is_null($key)) {
            $result = $this->select([$column])->get();
        } else {
            $result = $this->select([$column, $key])->get();
        }

        $collect = [];

        foreach ($result as $as => $value) {
            if (count($value) > 1) {
                $collect[$value[$key]] = $value[$column];
            } else {
                $collect[] = $value[$column];
            }
        }

        return $collect;
    }

    /**
     * Get the maximum value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function max($column, $as = 'max')
    {
        $this->bind('select', "SELECT MAX($column) AS $as FROM");

        return $this->get()[0][$as];
    }

    /**
     * Get the minimum value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function min($column, $as = 'min')
    {
        $this->bind('select', "SELECT MIN($column) AS $as FROM");

        return $this->get()[0][$as];
    }

    /**
     * Compute the average value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function avg($column, $as = 'avg')
    {
        $this->bind('select', "SELECT AVG($column) AS $as FROM");

        return $this->get()[0][$as];
    }

    /**
     * Compute the sum of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function sum($column, $as = 'sum')
    {
        $this->bind('select', "SELECT SUM($column) AS $as FROM");

        return $this->get()[0][$as];
    }

    /**
     * Insert new record into the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function insert($attributes = [])
    {
        return $this->connection->insert($this->getBindings(), $attributes);
    }

    /**
     * Update record in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function update($attributes = [])
    {
        return $this->connection->update($this->getBindings(), $attributes);
    }

    /**
     * Delete record from the database.
     *
     * @param int|null $id
     * @return bool
     */
    public function delete($id = null)
    {
        if (! is_null($id)) {
            $this->where('id', $id);
        }

        return $this->connection->delete($this->getBindings());
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return \Foundation\Database\Query\Builder
     */
    public function newQuery()
    {
        return new static($this->connection);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }
    }
}