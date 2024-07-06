<?php

namespace Foundation\Http;

use InvalidArgumentException;

class JsonResponse
{
    use ResponseHelpers;

    /**
     * Options for JSON encoding.
     *
     * @var int
     */
    protected $encodingOptions;

    /**
     * Create a new json response instance.
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return void
     * 
     */
    public function __construct($data = null, $status = 200, array $headers = [], $options = 0)
    {
        $this->data = $data;
        $this->setStatusCode($status);
        $this->setMessageCode($status);
        $this->setHeaders($headers);
        $this->encodingOptions = $options;
    }

    /**
     * Convert data to json.
     *
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function toJson()
    {
        $result = [
            'status'  => $this->status,
            'message' => $this->message,
            'data'    => $this->data,
        ];

        $json = json_encode($result);

        if (! $this->hasValidJson(json_last_error())) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        http_response_code($this->status);

        foreach ($this->headers as $header) {
            header($header);
        }

        return $json;
    }

    /**
     * Determine if an error occurred during JSON encoding.
     *
     * @param int $jsonError
     * @return bool
     */
    protected function hasValidJson($jsonError)
    {
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
                    in_array($jsonError, [
                        JSON_ERROR_RECURSION,
                        JSON_ERROR_INF_OR_NAN,
                        JSON_ERROR_UNSUPPORTED_TYPE,
                    ]);
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @param int $option
     * @return bool
     */
    protected function hasEncodingOption($option)
    {
        return (bool) ($this->encodingOptions & $option);
    }
}