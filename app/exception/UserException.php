<?php

namespace app\exception;

use sek\{BaseException, HttpCode};

class UserException extends BaseException
{
    protected $code = HttpCode::SC_BAD_REQUEST;

    protected $message = 'Validation Relative error';

    protected $errcode = 100000;

    protected $errcodes = [
        100001 => [HttpCode::SC_NOT_FOUND, 'user not found']
    ];
}