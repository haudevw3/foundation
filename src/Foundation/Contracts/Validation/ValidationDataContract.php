<?php

namespace Foundation\Contracts\Validation;

use Foundation\Contracts\Database\QueryBuilder;

interface ValidationDataContract
{
    /**
     * Validate the data according to general rules and return the array of validated data.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function validate(QueryBuilder $query, array $data, array $rules);

    /**
     * Checks if the given value is null.
     *
     * @param string $value
     * @return bool
     */
    public function required($value);

    /**
     * Checks whether the given value is less than the length of the rule.
     *
     * @param string $value
     * @param int $length
     * @return bool
     */
    public function min($value, $length);

    /**
     * Checks if the given value is greater than the length of the rule.
     *
     * @param string $value
     * @param int $length
     * @return bool
     */
    public function max($value, $length);

    /**
     * Checks if the given value has lowercase and uppercase letters according to the given rule.
     *
     * @param string $value
     * @return bool
     */
    public function alpha($value);

    /**
     * Checks if the given value has lowercase and uppercase letters and numbers with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function alpha_dash($value);

     /**
     * Checks if the given value is in the correct email format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function email($value);

     /**
     * Checks if the given value is in the correct integer format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function integer($value);

    /**
     * Checks if the given value is in the correct numeric format according to the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function numeric($value);

    /**
     * Checks if the given value is in the correct url format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function url($value);

    /**
     * Checks if the given value is the only value in the database with the given rule.
     *
     * @param \Foundation\Database\Query\Builder $query
     * @param string $explode
     * @param string $value
     * @return bool
     */
    public function unique($query, $explode, $value);

    /**
     * Checks if the given value exists in the database with the given rule.
     *
     * @param \Foundation\Database\Query\Builder $query
     * @param string $explode
     * @param string $value
     * @return bool
     */
    public function exists($query, $explode, $value);

    /**
     * Checks if the given value is in the correct phone format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function phone($value);
}