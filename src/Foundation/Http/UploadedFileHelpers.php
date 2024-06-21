<?php

namespace Foundation\Http;

use Foundation\Support\Str;

class UploadedFileHelpers
{
    protected static $mimeTypes = [
        'image/jpeg',
        'image/png',
        'audio/mpeg',
        'application/pdf',
        'application/msword',
    ];

    protected static $fileTypes = [
        'jpg' => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png' => 'PNG Image',
        'mp3' => 'MPEG Audio',
        'pdf' => 'Adobe Acrobat',
        'doc' => 'Microsoft Word',
    ];

    /**
     * Checks if the uploaded file is valid.
     *
     * @param string $mimeType
     * @return bool
     */
    public static function isValid($mimeType)
    {
        return in_array($mimeType, self::$mimeTypes);
    }

    /**
     * Hash an for the file.
     *
     * @param string $extension
     * @return string
     */
    public static function hashFile($extension)
    {
        return Str::random(40).'.'.$extension;
    }
}