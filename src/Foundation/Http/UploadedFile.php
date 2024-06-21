<?php

namespace Foundation\Http;

use Foundation\Contracts\Http\UploadedFileContract;

class UploadedFile implements UploadedFileContract
{
    /**
     * The original name of the uploaded file.
     *
     * @var string
     */
    protected $originalName;

    /**
     * The mime type of the uploaded file.
     *
     * @var string
     */
    protected $mimeType;

    /**
     * The absolute path to the file on the server.
     *
     * @var string
     */
    protected $realPath;

    /**
     * The error code.
     *
     * @var int
     */
    protected $error;

    /**
     * The size of the uploaded file in bytes.
     *
     * @var int
     */
    protected $size;

    /**
     * The cache copy of the hash file.
     *
     * @var string
     */
    protected $hash = null;

    /**
     * Create a new uploaded file instance.
     *
     * @param string $originalName
     * @param string $mimeType
     * @param string $realPath
     * @param int $error
     * @param int $size
     * @return void
     */
    public function __construct($originalName, $mimeType, $realPath, $error, $size)
    {
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->realPath = $realPath;
        $this->error = $error;
        $this->size = $size;
    }

    /**
     * Get the original name of the uploaded file.
     *
     * @return string
     */
    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Get the original file extension of the uploaded file.
     *
     * @return string
     */
    public function getClientOriginalExtension()
    {
        $explode = explode('.', $this->originalName);

        $extension = end($explode);

        return $extension;
    }

    /**
     * Get the mime type of the uploaded file.
     *
     * @return string
     */
    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Get the size of the uploaded file in bytes.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get the absolute path to the file on the server.
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->realPath;
    }

    /**
     * Checks if the uploaded file is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return UploadedFileHelpers::isValid($this->getClientMimeType());
    }

    /**
     * Hash an for the file.
     *
     * @return $this
     */
    public function hash()
    {
        $this->hash = UploadedFileHelpers::hashFile($this->getClientOriginalExtension());

        return $this;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $path
     * @param string $name
     * @return void
     */
    public function move($path, $name = null)
    {
        $path = base_path($path);

        $this->saveTo($path, $name);
    }

    /**
     * Store the uploaded file on the default disk.
     *
     * @param string $path
     * @param string $name
     * @return void
     */
    public function store($path = null, $name = null)
    {   
        $path = is_null($path) ? $this->getDisk()['root'] : storage_path($path);

        $this->saveTo($path, $name);
    }

    /**
     * Save the uploaded file on disk with a specific path and name.
     *
     * @param string $path
     * @param string $name
     * @return void
     */
    protected function saveTo($path, $name)
    {
        $name = is_null($name)
                ? (! is_null($this->hash) ? $this->hash : $this->getClientOriginalName())
                : $name.'.'.$this->getClientOriginalExtension();

        move_uploaded_file($this->getRealPath(), trim($path).DIRECTORY_SEPARATOR.$name);
    }

    /**
     * Get the configuration of the default disk.
     *
     * @return array
     */
    protected function getDisk()
    {
        $default = config('filesystems.default');

        $disk = config("filesystems.disks.$default");

        return $disk;
    }
}