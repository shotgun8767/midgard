<?php

namespace tracer\exception;

use RuntimeException;
use Throwable;

class ClassNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $class;

    public function __construct(string $class, ?string $message = null, Throwable $previous = null)
    {
        if (!$message) {
            $message = "Class not exists: [$class]";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * get class name which is not found
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }
}