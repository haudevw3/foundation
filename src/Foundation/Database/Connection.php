<?php

namespace Foundation\Database;

use Foundation\Contracts\Database\ConnectionContract;
use PDO;
use PDOException;

class Connection implements ConnectionContract
{
    /**
     * The active PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * Set up configurations and create connections to the database.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->setDefaultConnection();
    }

    /**
     * Get the default connection name.
     *
     * @return array
     */
    protected function getDefaultConnection()
    {
        $default = config('database.default');

        return config("database.$default");
    }

    /**
     * Get the PDO connection instance.
     *
     * @return \PDO
     * 
     * @throws \PDOException
     */
    protected function setDefaultConnection()
    {
        $connection = $this->getDefaultConnection();

        $driver   = $connection['driver'];
        $host     = $connection['host'];
        $database = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        $options  = $connection['options'];

        $dsn = "$driver:dbname=$database;host=$host";

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    protected function getPdo()
    {
        return $this->pdo;
    }
    
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
    public function statement($query, $attributes = [], $useStatementPdo = false)
    {
        try {
            $statement = $this->getPdo()->prepare($query);

            if (empty($attributes)) {
                $result = $statement->execute();
            } else {
                $result = $statement->execute($attributes);
            }

            if ($useStatementPdo && $result) {
                return $statement;
            }

            return $result;

        } catch (PDOException $e) {
            throw new PDOException("SQL Query: $query ".$e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Run an insert statement against the database.
     *
     * @param array $bindings
     * @param array $attributes
     * @return bool
     */
    public function insert($bindings = [], $attributes = [])
    {
        $table = $bindings['table'];

        $keys = array_keys($attributes);
        $values = ':'.implode(', :', array_values($keys));

        $columns = implode(', ', $keys);

        $query = "INSERT INTO $table ($columns) VALUES ($values)";

        return $this->statement($query, $attributes);
    }

    /**
     * Run an update statement against the database.
     *
     * @param array $bindings
     * @param array $attributes
     * @return bool
     */
    public function update($bindings = [], $attributes = [])
    {
        $table = $bindings['table'];
        $where = $bindings['where'];

        $columns = '';

        foreach ($attributes as $key => $value) {
            $columns .= "$key=:$key,";
        }

        $columns = rtrim($columns, ',');

        $query = "UPDATE $table SET $columns $where";

        return $this->statement($query, $attributes);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param array $bindings
     * @return bool
     */
    public function delete($bindings = [])
    {
        $table = $bindings['table'];
        $where = $bindings['where'];

        $query = "DELETE FROM $table $where";

        return $this->statement($query);
    }

    /**
     * Fetch a single result from the executed statement.
     *
     * @param string $query
     * @return array|null
     */
    public function fetch($query)
    {
        $data = $this->statement($query, [], true)
                     ->fetch(PDO::FETCH_ASSOC);

        return  $data ?: null;
    }

    /**
     * Fetch all results from the executed statement.
     *
     * @param string $query
     * @return array|null
     */
    public function fetchAll($query)
    {
        $data = $this->statement($query, [], true)
                     ->fetchAll(PDO::FETCH_ASSOC);

        return $data ?: null;
    }
}