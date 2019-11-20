<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\model\User as model;
use app\model\user\UserAuthor;
use sek\Package;
use tracer\Reflect;

class User extends BaseApi
{
    public function getOnMethod(string $method)
    {
        if ($method == 'f') {
            return $this->f();
        }

        $reflect = new Reflect(model::class, $method);

        $res = $reflect();

        if (!is_array($res)) {
            $res = [
                'result' => $res
            ];
        }

        return Package::ok('成功！', $res);
    }

    public function f()
    {
        $model = new UserAuthor();
        $res = $model->status('ABNORMAL')->getArray();

        return Package::ok('成功！', $res);
    }
}