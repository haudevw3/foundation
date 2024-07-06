<?php

namespace Foundation\Validation;

use Foundation\Contracts\Validation\RuleNotFoundException;

class CommonRules
{
    /**
     * General set of rules for data validation.
     *
     * @var array
     */
    protected static $rules = [
        'required', 'filled', 'nullable', 'email',
        'numeric', 'integer', 'string', 'array',
        'date', 'date_format', 'before', 'after',
        'confirmed', 'unique', 'exists', 'min',
        'max', 'regex', 'size', 'in', 'not_in',
        'accepted', 'active_url', 'alpha', 'alpha_dash',
        'alpha_num', 'distinct', 'digits', 'digits_between',
        'phone', 'except'
    ];

    /**
     * Get all the rules.
     *
     * @return array
     */
    public static function all()
    {
        return self::$rules;
    }

    /**
     * Checks whether a given rule exists in the common rule set.
     *
     * @param string $rule
     * @return bool
     */
    public static function isRule($rule)
    {
        return in_array($rule, self::all());
    }

    /**
     * An exception occurs when the given rule is invalid.
     *
     * @param string $rule
     * @return void
     * 
     * @throws \Foundation\Contracts\Validation\RuleNotFoundException
     */
    public static function throwRuleException($rule)
    {
        if (! self::isRule($rule)) {
            throw new RuleNotFoundException("Rule [$rule] not found in the common rule set.");
        }
    }
}