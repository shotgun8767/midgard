<?php

namespace app\exception;

use sek\{BaseException, HttpCode};

class TokenException extends BaseException
{
    protected $code = HttpCode::SC_FORBIDDEN;

    protected $message = 'Token Relative error';

    protected $errcode = 50000;

    protected $errcodes = [
        50001 => [HttpCode::SC_FORBIDDEN, '用户所绑定的令牌权限不足以访问当前api！'],
        50002 => [HttpCode::SC_FORBIDDEN, '头信息（header）中不含有令牌或不能被解析，拒绝访问！'],
        50003 => [HttpCode::SC_FORBIDDEN, '令牌已过期，请重新获取！'],
    ];
}