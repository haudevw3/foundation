<?php

namespace Foundation\Contracts\Http;

interface UploadedFileContract
{
    /**
     * Get the original name of the uploaded file.
     *
     * @return string
     */
    public function getClientOriginalName();

    /**
     * Get the original file extension of the uploaded file.
     *
     * @return string
     */
    public function getClientOriginalExtension();

    /**
     * Get the mime type of the uploaded file.
     *
     * @return string
     */
    public function getClientMimeType();

    /**
     * Get the size of the uploaded file in bytes.
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the absolute path to the file on the server.
     *
     * @return string
     */
    public function getRealPath();

    /**
     * Checks if the uploaded file is valid.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Hash an for the file.
     *
     * @return $this
     */
    public function hash();

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $path
     * @param string $name
     * @return void
     */
    public function move($path, $name = null);

    /**
     * Store the uploaded file on the default disk.
     *
     * @param string $path
     * @param string $name
     * @return void
     */
    public function store($path = null, $name = null);

    // /**
    //  * Guesses the extension based on the file's mime type.
    //  *
    //  * @return string
    //  */
    // public function guessExtension();
}