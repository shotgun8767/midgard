<?php

namespace app\validate;

use think\Validate;

class ValidateRule extends Validate
{
    /**
     * 是否为非负数
     * @param $val
     * @return bool
     */
    protected function unsigned($val)
    {
        if (empty($val)) return true;
        return $val == 0 or $this->id($val) ? true : false;
    }

    /**
     * 判断是否正整数
     * @param $val
     * @return bool
     */
    protected function id($val)
    {
        if (empty($val)) return true;
        return is_numeric($val) && $val > 0 && !strpos($val, '.') ? true : false;
    }

    /**
     * 检测是否手机号码（11位数字）
     * @param $val
     * @return bool
     */
    protected function tel($val)
    {
        if (empty($val)) return true;
        return strlen($val) == 11 && $this->id($val) ? true : false;
    }
}