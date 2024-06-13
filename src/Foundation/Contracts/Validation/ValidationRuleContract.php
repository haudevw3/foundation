<?php

namespace Foundation\Contracts\Validation;

interface ValidationRuleContract
{
    /**
     * Analyze the chain of rules and evaluate whether they are general rules or not.
     * Returns the array of rules and messages as rules.
     *
     * @param array $rules
     * @return array
     */
    public function validate(array $rules);
}