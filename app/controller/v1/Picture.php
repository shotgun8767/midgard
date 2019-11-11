<?php

namespace app\controller\v1;

use app\api\BaseApi;
use app\exception\UserException;
use app\model\Picture as model;
use sek\Package;

class Picture extends BaseApi
{
    public function getPicture(int $pictureId)
    {
        $res = (new model)->getInfo($pictureId);

        return $res ?
            Package::ok('成功获取图片信息', $res) :
            Package::error(UserException::class, 100001);
    }
}