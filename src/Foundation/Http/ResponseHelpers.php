<?php

namespace Foundation\Http;

use InvalidArgumentException;

trait ResponseHelpers
{
    /**
     * Response data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Response status code.
     *
     * @var int
     */
    protected $status;

    /**
     * Response message.
     *
     * @var string
     */
    protected $message;

    /**
     * Response headers.
     *
     * @var array
     */
    protected $headers = ['Content-Type: application/json'];

    /**
     * HTTP status codes and corresponding messages.
     *
     * @var array
     */
    protected $codes = [
        200 => 'Success',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    ];

    /**
     * Set the message on the response.
     *
     * @param int $status
     * @return void
     */
    protected function setMessageCode($status)
    {
        $this->message = $this->codes[$status];
    }

    /**
     * Set the status code on the response.
     *
     * @param int $status
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    protected function setStatusCode($status)
    {
        if (! isset($this->codes[$status])) {
            throw new InvalidArgumentException("Status code [$status] is invalid.");
        }

        $this->status = $status;
    }

    /**
     * Set the headers on the response.
     *
     * @param array $headers
     * @return void
     */
    protected function setHeaders(array $headers = [])
    {
        if (! empty($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }
    }
}