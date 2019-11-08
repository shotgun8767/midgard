<?php

namespace app\exception;

use sek\{BaseException, HttpCode};

class BackendException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Backend Relative error';

    protected $errcode = 10000;

    protected $errcodes = [
        10001 => [HttpCode::SC_BAD_REQUEST, '没有匹配到路由或请求方法错误！'],
    ];
}