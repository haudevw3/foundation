<?php

namespace Foundation\Http;

abstract class FormRequest extends Request
{
    /**
     * Determines whether the user is authorized to make the current request.
     *
     * @var bool
     */
    protected $authorized;

    /**
     * The rules apply to the current request.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Error messages for the current request's rule.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create a new instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->rules = $this->rules();
        $this->messages = $this->messages();
        $this->authorized = $this->authorized();
    }
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorized();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Get custom messages for validator errors.
     * 
     * @return array
     */
    abstract public function messages();
    
    /**
     * Get the validated data from the request.
     * If the data is successfully validated, true is returned.
     * If the data being validated is not valid, an error array is returned.
     *
     * @return array|bool
     */
    public function validated()
    {
        $validator = $this->validator->make(
            $this->all(), $this->rules, $this->messages
        );

        if ($validator->fails()) {
            return $validator->errors();
        }

        return $validator->passes();
    }
}