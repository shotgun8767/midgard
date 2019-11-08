<?php

namespace app\middleware;

use Closure;
use app\controller\ApiHandle;
use think\facade\Request;

/**
 * Class Api Api处理中间件
 * @package app\middleware
 */
class Api
{
    /**
     * @param $request
     * @param Closure $next
     * @param array $param
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle($request, Closure $next, array $param)
    {
        $version = Request::param('version', null);
        ApiHandle::apiCore($param['controller'], $param['method'], $version);

        return $next($request);
    }
}