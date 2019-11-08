<?php

namespace tracer\exception;

use RuntimeException;
use Throwable;

class MethodNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $method;

    public function __construct(string $method, ?string $message = null, Throwable $previous = null)
    {
        if (!$message) {
            $message = "Method not exists: [$method]";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * get method name which is not found
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }
}