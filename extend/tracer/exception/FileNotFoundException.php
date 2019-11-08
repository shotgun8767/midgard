<?php

namespace tracer\exception;

use RuntimeException;
use Throwable;

class FileNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $filepath;

    public function __construct(string $filepath, ?string $message = null, Throwable $previous = null)
    {
        if (!$message) {
            $message = "File not exists: [$filepath]";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * get filepath which is not found
     * @return string
     */
    public function getFilepath() : string
    {
        return $this->filepath;
    }
}