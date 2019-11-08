<?php

namespace app\exception;

use sek\{BaseException, HttpCode};

class DataBaseException extends BaseException
{
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    protected $message = 'Database Relative error';

    protected $errcode = 20000;

    protected $errcodes = [
        20001 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '数据库获取单条记录时发生错误！'],
        20002 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '数据库获取多条记录时发生错误！'],
        20003 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '数据库更新信息时发生错误！'],
        20004 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '数据库插入信息时发生错误！'],
        20005 => [HttpCode::SC_INTERNAL_SERVER_ERROR, '数据库移除数据时发生错误！'],
    ];
}