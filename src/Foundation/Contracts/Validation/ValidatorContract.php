<?php

namespace Foundation\Contracts\Validation;

interface ValidatorContract
{
    /**
     * The validator instance configured with the provided data, rules, and custom error messages.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return $this
     */
    public function make(array $data, array $rules, array $messages);

    /**
     * Get the attributes and values that were validated.
     *
     * @return array
     */
    public function validated();

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails();

    /**
     * Get the attributes and values that were failed.
     *
     * @return array
     */
    public function failed();

    /**
     * Determine if the validation rule passes.
     *
     * @return bool
     */
    public function passes();

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors();
}