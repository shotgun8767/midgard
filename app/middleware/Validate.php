<?php

namespace app\middleware;

use app\route\Route;
use app\validate\ValidateCore;
use Closure;
use think\facade\Request;
use srp\component\Param;
use app\controller\ApiHandle;
use app\exception\ValidationException;

/**
 * Class Validate 验证器中间件
 * @package app\middleware
 */
class Validate
{
    protected const TYPE_DELIMITER = '#';
    protected const VALIDATE_RULE_DELIMITER = '@';

    /**
     * @param $request
     * @param Closure $next
     * @param array $n
     * @return mixed
     * @throws ValidationException
     * @throws \ReflectionException
     * @throws \sek\BaseException
     */
    public function handle($request, Closure $next, array $n = [])
    {
        $Route = $n['Route'];
        if ($Route instanceof Route) {
            $vParam = $Route->getParam();
            $vPost = $Route->getPost();
        } else {
            throw new ValidationException(40001);
        }

        $post = Request::post();
        $param = Request::param();

        $func_check = function (array $vParam, &$param) {
            if (!$vParam) return;
            foreach ($vParam as $name => $Param) {
                if (!($Param instanceof Param)) continue;

                # 处理参数不存在的情况
                if (!key_exists($name, $param)) {
                    if ($Param->getRequire() && $Param->getType() !== 'file') {
                        throw new ValidationException(40008, "missing require param '$name'!");
                    } else {
                        if (($def = $Param->getDefault()) !== null) {
                            $param[$name] = $def;
                        } else continue;
                    }
                }

                # 校验参数值类型
                $type = $Param->getType();
                $value = $param[$name];
                $this->validateType($name, $value, $type);

                # 校验参数值长度
                $maxLen = $Param->getMaxLen();
                if ($maxLen && $maxLen < strlen($value)) {
                    throw new ValidationException(40002, "param validation error: length of param '$name' over the maximum!");
                }
            }
        };

        $func_check($vParam, $param);
        $func_check($vPost, $post);

        # 校验参数
        $validate = $Route->getValidate();
        $this->validate($validate['param']??[], $param);
        $this->validate($validate['post']??[], $post);

        # 传递Api核心参数
        $param = array_diff_key(array_merge($param, $post), ['version' => null]);
        ApiHandle::apiCore()->setParam($param);

        return $next($request);
    }

    /**
     * 校验参数值是否符合类型
     * @param string $name
     * @param string $value
     * @param string $type
     * @throws ValidationException
     */
    protected function validateType(string $name, string $value, string $type)
    {
        switch ($type) {
            case 'string' :
                if (!$this->checkString($value)) {
                    throw new ValidationException(40003, "param '$name' has illegal chars!");
                }
                break;
            case 'bool' :
                $value = strtolower($value);
                if (!in_array($value, [0, 1, 'true', 'false'])) {
                    throw new ValidationException(40004, "param '$name' should be boolean!");
                }
                break;
            case 'int' :
                if (!is_numeric($value) || (int)$value != $value) {
                    throw new ValidationException(40005, "param '$name' should be integer!");
                }
                break;
            case 'float' :
                if (!is_numeric($value) || !strpos($value, '.')) {
                    throw new ValidationException(40006, "param '$name' should be float!");
                }
                break;
            case 'file' : break;
        }
    }

    /**
     * @param array $rules
     * @param array $params
     * @throws \sek\BaseException
     */
    protected function validate(array $rules, array $params)
    {
        if (!$rules) return;

        $ValidateCore = new ValidateCore;
        foreach ($rules as $name => $rule) {
            $ValidateCore->load($name, $rule);
        }
        $res = $ValidateCore->check($params);

        if (true !== $res) {
            $error = $ValidateCore->getError();
            throw (new ValidationException(40007, 'params validation fails!'))->setData($error);
        }
    }


    /**
     * 检测字符串是否含有非法字符
     * @param $str
     * @return bool
     */
    protected function checkString($str)
    {
        if (empty($str)) return true;

        // 检测XXS攻击
        $ra = [
            '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/',
            '/script/',
            '/javascript/',
            '/vbscript/',
            '/expression/',
            '/applet/',
            '/meta/',
            '/xml/',
            '/blink/',
            '/link/',
            '/style/',
            '/embed/',
            '/object/',
            '/frame/',
            '/layer/',
            '/title/',
            '/bgsound/',
            '/base/',
            '/onload/',
            '/onunload/',
            '/onchange/',
            '/onsubmit/',
            '/onreset/',
            '/onselect/',
            '/onblur/',
            '/onfocus/',
            '/onabort/',
            '/onkeydown/',
            '/onkeypress/',
            '/onkeyup/',
            '/onclick/',
            '/ondblclick/',
            '/onmousedown/',
            '/onmousemove/',
            '/onmouseout/',
            '/onmouseover/',
            '/onmouseup/',
            '/onunload/'
        ];
        if ($str != preg_replace($ra, '', $str)) {
            return false;
        }
        return true;
    }
}