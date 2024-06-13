<?php

namespace Foundation\Contracts\Database;

interface ConnectionContract
{
    /**
     * Execute query statement.
     *
     * @param string $query
     * @param array $attributes
     * @param bool $useStatementPdo
     * @return bool|\PDOStatement
     * 
     * @throws \PDOException
     */
    public function statement($query, $attributes = [], $useStatementPdo = false);

    /**
     * Run an insert statement against the database.
     *
     * @param array $bindings
     * @param array $attributes
     * @return bool
     */
    public function insert($bindings = [], $attributes = []);

    /**
     * Run an update statement against the database.
     *
     * @param array $bindings
     * @param array $attributes
     * @return bool
     */
    public function update($bindings = [], $attributes = []);

    /**
     * Run a delete statement against the database.
     *
     * @param array $bindings
     * @return bool
     */
    public function delete($bindings = []);

    /**
     * Fetch a single result from the executed statement.
     *
     * @param string $query
     * @return array|null
     */
    public function fetch($query);

    /**
     * Fetch all results from the executed statement.
     *
     * @param string $query
     * @return array|null
     */
    public function fetchAll($query);
}