<?php

namespace app\controller;

use app\api\ApiCore;
use sek\Package;
use sek\BaseException;

/**
 * Api核心处理文件
 *
 * Class ApiHandle
 * @package app\controller
 */
class ApiHandle
{
    /**
     * Api核心
     * @var ApiCore
     */
    public static $ApiCore;

    /**
     * Api入口函数
     * @throws BaseException
     * @throws \ReflectionException
     */
    public function index() : void
    {
        $Package = self::$ApiCore->call();

        if ($Package instanceof Package) {
            $Package->throw();
        } else {
            Package::noContent()->throw();
        }
    }

    /**
     * 获取Api核心
     * @param string $controller
     * @param string $method
     * @param string|null $version
     * @return ApiCore
     * @throws \ReflectionException
     */
    public static function apiCore(string $controller = '', string $method = '', ?string $version = null)
    {
        if (is_null(self::$ApiCore)) {
            self::$ApiCore = new ApiCore($controller, $method, $version);
        }

        return self::$ApiCore;
    }
}