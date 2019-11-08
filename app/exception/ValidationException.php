<?php

namespace app\exception;

use sek\{BaseException, HttpCode};

class ValidationException extends BaseException
{
    protected $code = HttpCode::SC_BAD_REQUEST;

    protected $message = 'Validation Relative error';

    protected $errcode = 40000;

    protected $errcodes = [
        40001 => [HttpCode::SC_INTERNAL_SERVER_ERROR, 'param validation error: fatal error!'],
        40002 => [HttpCode::SC_BAD_REQUEST, 'param validation error: length of param over the maximum!'],
        40003 => [HttpCode::SC_BAD_REQUEST, 'param has illegal chars!'],
        40004 => [HttpCode::SC_BAD_REQUEST, 'param should be boolean!'],
        40005 => [HttpCode::SC_BAD_REQUEST, 'param should be integer!'],
        40006 => [HttpCode::SC_BAD_REQUEST, 'param should be float!'],
        40007 => [HttpCode::SC_UNPROCESSABLE_ENTITY, 'params validation fails!'],
        40008 => [HttpCode::SC_BAD_REQUEST, 'missing require param!']
    ];
}