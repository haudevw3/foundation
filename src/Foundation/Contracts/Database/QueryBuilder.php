<?php

namespace Foundation\Contracts\Database;

interface QueryBuilder
{
    /**
     * Select columns to query.
     *
     * @param array $columns
     * @return $this
     */
    public function select($columns = []);

    /**
     * Table to be queried.
     *
     * @param string $table
     * @return $this
     */
    public function table($table);

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null);

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function andWhere($column, $operator = null, $value = null);

    /**
     * Condition to filter results.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null);

    /**
     * Sort results by a specific column.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * Group the results by one or more columns.
     *
     * @param array|string $groups
     * @return $this
     */
    public function groupBy(...$groups);

    /**
     * Condition to filter grouped results.
     *
     * @param string $column
     * @param string|null $operator
     * @param mixed $value
     * @return $this
     */
    public function having($column, $operator = null, $value = null);

    /**
     * Add table to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return void
     */
    public function join($table, $first, $operator = null, $second = null);

    /**
     * Add a LEFT JOIN to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null);

    /**
     * Add a RIGHT JOIN to the query.
     *
     * @param string $table
     * @param string|Closure $first
     * @param string|null $operator
     * @param mixed $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null);

    /**
     * Limit the number of records returned.
     *
     * @param int $value
     * @return $this
     */
    public function limit($value);

    /**
     * Skip a number of records from the beginning of the results.
     *
     * @param int $value
     * @return $this
     */
    public function offset($value);

    /**
     * Limit the number of records returned.
     *
     * @param int $value
     * @return $this
     */
    public function take($value);

    /**
     * Skip a number of records from the beginning of the results.
     *
     * @param int $value
     * @return $this
     */
    public function skip($value);

    /**
     * Get all results of the query.
     *
     * @param array $columns
     * @return array|null
     */
    public function get($columns = []);

    /**
     * Get the first record of the query.
     *
     * @param array $columns
     * @return array|null
     */
    public function first($columns = []);

    /**
     * Find a record by the primary key.
     *
     * @param int $id
     * @param array $columns
     * @return array|null
     */
    public function find($id, $columns = []);

    /**
     * 
     * Count the number of records that satisfy the condition, if any.
     *
     * @param string $column
     * @param string $as
     * @return int
     */
    public function count($column = '*', $as = 'count');

    /**
     * Get an array of values from a specific column.
     *
     * @param string $column
     * @param string|null $key
     * @return array|null
     */
    public function pluck($column, $key = null);

    /**
     * Get the maximum value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function max($column, $as = 'max');

    /**
     * Get the minimum value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function min($column, $as = 'min');

    /**
     * Compute the average value of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function avg($column, $as = 'avg');

    /**
     * Compute the sum of a specific column.
     *
     * @param string $column
     * @param string $as
     * @return mixed
     */
    public function sum($column, $as = 'sum');

    /**
     * Insert new record into the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function insert($attributes = []);

    /**
     * Update record in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function update($attributes = []);

    /**
     * Delete record from the database.
     *
     * @param int|null $id
     * @return bool
     */
    public function delete($id = null);
}