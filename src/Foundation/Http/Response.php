<?php

namespace Foundation\Http;

class Response
{
    // /**
    //  * Create a new HTTP response.
    //  *
    //  * @param mixed $content
    //  * @param int $status
    //  * @param array $headers
    //  * @return void
    //  * 
    //  * @throws \InvalidArgumentException
    //  */
    // public function __construct($content = '', $status = 200, array $headers = [])
    // {
    //     $this->content = $content;
    //     $this->setStatusCode($status);
    //     $this->setHeaders($headers);
    // }

    /**
     * Convert data to json.
     * 
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return void
     */
    public function json($data = null, $status = 200, array $headers = [])
    {
        echo (new JsonResponse($data, $status, $headers))->toJson();
    }
}
