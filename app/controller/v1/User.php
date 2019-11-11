<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\UserException;
use app\model\User as model;
use sek\Package;

class User extends BaseApi
{
    public function getInfo(int $userId)
    {
        $res = (new model)->getInfo($userId);

        return $res ?
            Package::ok('成功获取用户信息', $res) :
            Package::error(UserException::class, 100001);
    }
}