<?php

namespace tracer\exception;

use RuntimeException;
use Throwable;

class FunctionNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $function;

    public function __construct(string $function, ?string $message = null, Throwable $previous = null)
    {
        if (!$message) {
            $message = "Function not exists: [$function]";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * get function name which is not found
     * @return string
     */
    public function getFunction() : string
    {
        return $this->function;
    }
}