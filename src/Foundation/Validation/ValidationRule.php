<?php

namespace Foundation\Validation;

use Foundation\Contracts\Validation\ValidationRuleContract;

class ValidationRule implements ValidationRuleContract
{
    /**
     * Analyze the chain of rules and evaluate whether they are general rules or not.
     * Returns the array of rules and messages as rules.
     *
     * @param array $rules
     * @return array
     */
    public function validate(array $rules)
    {
        $result = [];

        foreach ($rules as $attribute => $ruleString) {
            $segments = explode('|', $ruleString);

            foreach ($segments as $segment) {
                $parts = explode(':', $segment);

                CommonRules::throwRuleException($parts[0]);

                $result['rules'][] = [
                    'rule'      => $parts[0],
                    'param'     => (count($parts) > 1) ? $parts[1] : $parts[0],
                    'attribute' => $attribute
                ];
            }
        }

        return $result;
    }
}