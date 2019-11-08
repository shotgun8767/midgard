<?php

namespace tracer\exception;

use RuntimeException;
use Throwable;

class ChainException extends RuntimeException
{
    public function __construct(?string $message = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}