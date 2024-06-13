<?php

namespace Foundation\Validation;

use Foundation\Contracts\Database\QueryBuilder;
use Foundation\Contracts\Session\SessionContract;
use Foundation\Contracts\Validation\ValidationDataContract;
use Foundation\Contracts\Validation\ValidationRuleContract;
use Foundation\Contracts\Validation\ValidatorContract;

class Validator implements ValidatorContract
{
    /**
     * The array of fallback.
     *
     * @var array
     */
    protected $fallback = [];

    /**
     * The Query\Builder instance.
     *
     * @var \Foundation\Contracts\Database\QueryBuilder
     */
    protected $query;

    /**
     * The Session instance.
     *
     * @var \Foundation\Contracts\Session\SessionContract
     */
    protected $session;

    /**
     * The ValidationRule instance.
     *
     * @var \Foundation\Contracts\Validation\ValidationRuleContract
     */
    protected $validationRule;

    /**
     * The ValidationData instance.
     *
     * @var \Foundation\Contracts\Validation\ValidationDataContract
     */
    protected $validationData;

    /**
     * Create a new Validator instance.
     *
     * @param \Foundation\Contracts\Database\QueryBuilder $query
     * @param \Foundation\Contracts\Session\SessionContract $session
     * @param \Foundation\Contracts\Validation\ValidationRuleContract $validationRule
     * @param \Foundation\Contracts\Validation\ValidationDataContract $validationData
     * 
     * @return void
     */
    public function __construct(QueryBuilder $query, SessionContract $session, 
                                ValidationRuleContract $validationRule, ValidationDataContract $validationData)
    {
        $this->query = $query;
        $this->session = $session;
        $this->validationRule = $validationRule;
        $this->validationData = $validationData;
    }

    /**
     * The validator instance configured with the provided data, rules, and custom error messages.
     * Run the validator's rules against its data.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return $this
     */
    public function make(array $data, array $rules, array $messages)
    {
        $validateRule = $this->validationRule->validate($rules);
        $validateData = $this->validationData->validate(
            $this->query, $data, $validateRule['rules']
        );

        $this->fallback = [
            'passes'    => $validateData['passes'] ?? false,
            'fails'     => $validateData['fails'] ?? false,
            'validated' => $validateData['valid_data'] ?? [],
            'failed'    => $validateData['invalid_data'] ?? [],
            'errors'    => isset($validateData['message_keys'])
                            ? array_map(function ($key) use ($messages) {
                                return $messages[$key];
                            }, $validateData['message_keys'])
                            : []
        ];
        
        $this->session->set('errors', $this->fallback['errors'] ?? null);

        return $this;
    }

    /**
     * Get the attributes and values that were validated.
     *
     * @return array
     */
    public function validated()
    {
        return $this->fallback['validated'];
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails()
    {
        return ! $this->passes();
    }

    /**
     * Get the attributes and values that were failed.
     *
     * @return array
     */
    public function failed()
    {
        return $this->fallback['failed'];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @return bool
     */
    public function passes()
    {
        return $this->fallback['passes'];
    }

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        return $this->fallback['errors'];
    }
}