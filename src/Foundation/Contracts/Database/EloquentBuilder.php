<?php

namespace Foundation\Contracts\Database;

interface EloquentBuilder
{
    /**
     * Get all records from the database.
     *
     * @return array
     */
    public function all($columns = []);

    /**
     * Find a record by primary key or raise an exception if not found.
     *
     * @param int $id
     * @param array $columns
     * @return array
     * 
     * @throws \Exception
     */
    public function findOrFail($id, $columns = []);

    /**
     * Add a new record into the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function create($attributes = []);

    /**
     * Update a record contained in the database based on id.
     *
     * @param int $id
     * @param array $attributes
     * @return bool
     */
    public function update($id, $attributes = []);

    /**
     * Delete a record present in the database based on id.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);

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
    public function paginate($perPage = null, $columns = [], $options = []);
}