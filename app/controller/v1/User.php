<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\model\User as model;
use sek\Package;
use tracer\Reflect;

class User extends BaseApi
{
    public function getOnMethod(string $method)
    {
        $reflect = new Reflect(model::class, $method);

        $res = $reflect();

        if (!is_array($res)) {
            $res = [
                'result' => $res
            ];
        }

        return Package::ok('成功！', $res);
    }
}