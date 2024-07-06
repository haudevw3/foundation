<?php

namespace Foundation\Validation;

use Foundation\Contracts\Database\QueryBuilder;
use Foundation\Contracts\Validation\ValidationDataContract;

class ValidationData implements ValidationDataContract
{
    /**
     * Validate the data according to general rules and return the array of validated data.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function validate(QueryBuilder $query, array $data, array $rules)
    {
        $result = [];

        foreach ($rules as $as) {
            $rule = $as['rule'];

            $param = $as['param'];

            $attribute = $as['attribute'];

            switch ($rule) {
                case 'required':
                    if ($this->required($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'min':
                    if (! $this->min($data[$attribute], $param)) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;
                
                case 'max':
                    if (! $this->max($data[$attribute], $param)) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'alpha':
                    if (! $this->alpha($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;
                
                case 'alpha_dash':
                    if (! $this->alpha_dash($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'email':
                    if (! $this->email($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'integer':
                    if (! $this->integer($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'numeric':
                    if (! $this->numeric($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;
                
                case 'url':
                    if (! $this->url($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'unique':
                    if (! $this->unique($query, $param, $data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'exists':
                    if ($this->exists($query, $param, $data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'except':
                    if ($this->except($query, $param, $data['id'], $data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;

                case 'phone':
                    if (! $this->phone($data[$attribute])) {
                        $this->mergeAttributeWithRule($result, $attribute, $rule);
                    }

                    break;
            }
        }

        $this->parseDataByValidationOutcome($result, $data);

        $this->feedbackValidationOutcome($result);

        return $result;
    }

    /**
     * Combine properties with rules.
     *
     * @param array $result
     * @param string $attribute
     * @param string $rule
     * @return void
     */
    protected function mergeAttributeWithRule(&$result, $attribute, $rule)
    {
        $result['invalid_rule'][$attribute][] = $rule;
    }

    /**
     * Parse data by validation outcome.
     *
     * @param array $result
     * @param array $data
     * @return void
     */
    protected function parseDataByValidationOutcome(&$result, $data)
    {
        foreach ($data as $key => $value) {
            if (isset($result['invalid_rule'][$key])) {
                $result['invalid_data'][$key] = $value;
            } else {
                $result['valid_data'][$key] = $value;
            }
        }
    }

    /**
     * Provide feedback on validation results based on the presence of invalid data and rules.
     * Build identification keys for validator.
     *
     * @param array $result
     * @return void
     */
    protected function feedbackValidationOutcome(&$result)
    {
        if (empty($result['invalid_rule'])) {
            $result['passes'] = true;
        } else {
            $result['fails'] = true;
        }

        if (! empty($result['invalid_rule'])) {
            foreach ($result['invalid_rule'] as $attribute => $rule) {
                $result['message_keys'][$attribute] = $attribute.'.'.$rule[0];
            }
        }
    }

    /**
     * Checks if the given value is null.
     *
     * @param string $value
     * @return bool
     */
    public function required($value)
    {
        return (strlen($value) == 0) ? true : false;
    }

    /**
     * Checks whether the given value is less than the length of the rule.
     *
     * @param string $value
     * @param int $length
     * @return bool
     */
    public function min($value, $length)
    {
        return (strlen($value) >= $length) ? true : false;
    }

     /**
     * Checks if the given value is greater than the length of the rule.
     *
     * @param string $value
     * @param int $length
     * @return bool
     */
    public function max($value, $length)
    {
        return (strlen($value) <= $length) ? true : false;
    }

    /**
     * Checks if the given value has lowercase and uppercase letters according to the given rule.
     *
     * @param string $value
     * @return bool
     */
    public function alpha($value)
    {
        return preg_match('/^[a-zA-z]+$/', $value) ? true : false;
    }

     /**
     * Checks if the given value has lowercase and uppercase letters and numbers with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function alpha_dash($value)
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $value) ? true : false;
    }

    /**
     * Checks if the given value is in the correct email format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function email($value)
    {
        return preg_match('/^[a-zA-Z0-9][\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/', $value) ? true : false;
    }

     /**
     * Checks if the given value is in the correct integer format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function integer($value)
    {
        return preg_match('/^-?\d+$/', $value) ? true : false;
    }

    /**
     * Checks if the given value is in the correct numeric format according to the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function numeric($value)
    {
        return preg_match('/^-?\d+$/', $value) ? true : false;
    }

    /**
     * Checks if the given value is in the correct url format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function url($value)
    {
        return preg_match('/^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$/', $value) ? true : false;
    }

    /**
     * Checks if the given value is the only value in the database with the given rule.
     *
     * @param \Foundation\Database\Query\Builder $query
     * @param string $explode
     * @param string $value
     * @return bool
     */
    public function unique($query, $explode, $value)
    {
        return;
    }

    /**
     * Checks if the given value exists in the database with the given rule.
     *
     * @param \Foundation\Database\Query\Builder $query
     * @param string $explode
     * @param string $value
     * @return bool
     */
    public function exists($query, $explode, $value)
    {
        $this->explodeExplicitRule($explode, $table, $column);

        $data = $query->table($table)->where($column, $value)->first();

        return ! empty($data) ? true : false;
    }

    /**
     * Checks if the given value exists in the database with the given rule.
     * Except itself.
     *
     * @param \Foundation\Database\Query\Builder $query
     * @param string $explode
     * @param string|int $id
     * @param string $value
     * @return bool
     */
    public function except($query, $explode, $id, $value)
    {
        $this->explodeExplicitRule($explode, $table, $column);

        $user = $query->table($table)->where('id', $id)->first([$column]);

        $bool = null;

        if ($user[$column] == $value) {
            $bool = false;
        } else {
            $bool = $this->exists($query, $explode, $value) ? true : false;
        }

        return $bool;
    }

    /**
     * Extract the explicit rule into a variable if necessary.
     *
     * @param string $explode
     * @param string $table
     * @param string $column
     * @return void
     */
    protected function explodeExplicitRule($explode, &$table, &$column)
    {
        $explode = explode(',', $explode);
        $table = $explode[0];
        $column = $explode[1];
    }

    /**
     * Checks if the given value is in the correct phone format with the given rules.
     *
     * @param string $value
     * @return bool
     */
    public function phone($value)
    {
        return preg_match('/^[0][0-9]{9}/', $value) ? true : false;
    }
}