<?php

namespace app\middleware;

use Closure;
use think\facade\Request;
use app\controller\ApiHandle;
use app\service\permission\User;
use app\exception\TokenException;

/**
 * Class Token Token中间件
 * @package app\middleware
 */
class Token
{
    /**
     * @param $request
     * @param Closure $next
     * @param array $param
     * @return mixed
     * @throws TokenException
     * @throws \ReflectionException
     * @throws \jwt\exception\TokenException
     */
    public function handle($request, Closure $next, array $param)
    {
        $permission = $param['permission'];

        if ($permission) {
            $token = Request::header('token');

            if (!$token) {
                throw new TokenException(50002);
            }

            $Token = new \app\service\Token(User::class, $token);

            if ($Token->isExpire()) {
                throw new TokenException(50003);
            }

            ApiHandle::apiCore()
                ->setLeastPermission($param['permission']??null)
                ->setToken($Token);
        }

        return $next($request);
    }
}