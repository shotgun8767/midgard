<?php

namespace app\api;

use app\service\Token;
use tracer\Reflect;
use app\exception\TokenException;
use sek\Package;

/**
 * Class ApiCore
 * @package app\api
 */
class ApiCore
{
    /**
     * 方法Reflect实例
     * @var Reflect
     */
    protected $Reflect;

    protected $Method;

    /**
     * 请求参数（包括PARAM和POST）
     * @var array
     */
    protected $param = [];

    /**
     * Token对象
     * @var Token
     */
    protected $Token;

    /**
     * 最低访问权限
     * @var string|null
     */
    protected $leastPermission;

    /**
     * 版本号
     * @var string
     */
    protected $version = '';

    /**
     * 是否支持分页
     * @var bool
     */
    protected $paginate = false;

    /**
     * ApiCore constructor.
     * @param string $controller
     * @param string $method
     * @param string|null $version
     * @throws \ReflectionException
     */
    public function __construct(string $controller, string $method, ?string $version = null)
    {
        $this->setMethod($controller, $method, $version);
    }

    /**
     * 设定：分页
     * @return $this
     */
    public function isPaginate() : self
    {
        $this->paginate = true;
        return $this;
    }

    /**
     * 设置Token
     * @param Token $_
     * @return ApiCore
     */
    public function setToken(Token $_) : self
    {
        $this->Token = $_;
        return $this;
    }

    /**
     * 设置param
     * @param array $param
     * @param bool $replace
     * @return ApiCore
     */
    public function setParam(array $param, bool $replace = false) : self
    {
        $this->param = $replace ? $param : array_merge($param, $this->param);
        return $this;
    }

    /**
     * 设置方法
     * @param string $controller
     * @param string $method
     * @param string|null $version
     * @return ApiCore
     * @throws \ReflectionException
     */
    public function setMethod(string $controller, string $method, ?string $version = null) : self
    {
        $controller = ucfirst($controller);

        if (is_null($version) && $this->version) {
            $version = $this->version;
        }

        if ($version) {
            $this->version = $version;
            $controller = $version . '\\' . $controller;
        }

        $this->Reflect = new Reflect('app\controller\\' . $controller, $method);
        return $this;
    }

    /**
     * 获取Token
     * @param string|null $permission
     * @return Token
     * @throws TokenException
     */
    public function token(?string $permission = null) : Token
    {
        static $reach = false;

        if ($permission) {
            if (!$this->Token->permissionReach($permission)) {
                throw new TokenException(50001);
            }
        } else {
            if ($this->leastPermission && !$reach) {
                if (!$this->Token->permissionReach($this->leastPermission)) {
                    throw new TokenException(50001);
                } else {
                    $reach = true;
                }
            }
        }

        return $this->Token;
    }

    /**
     * 获取参数
     * @param string|null $name 键名
     * @param mixed $default 默认值
     * @return mixed 参数值
     */
    public function param(?string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->param;
        } else {
            return $this->param[$name]??$default;
        }
    }

    /**
     * 查询参数是否存在
     * @param string $name 键名
     * @return bool
     */
    public function hasParam(string $name) : bool
    {
        return isset($this->param[$name]) ? true : false;
    }

    /**
     * 获取版本号
     * @return string
     */
    public function version() : string
    {
        return $this->version;
    }

    /**
     * 调用方法
     * @param ApiCore|null $ApiCore
     * @return Package|null
     * @throws \ReflectionException
     */
    public function call(?ApiCore $ApiCore = null) : ?Package
    {
        if (is_null($ApiCore)) {
            $ApiCore = $this;
        }

        return $this->Reflect
            ->instance([$ApiCore])
            ->invokeArgs($this->param);
    }

    /**
     * @param string $_
     * @return $this
     */
    public function setLeastPermission(?string $_)
    {
        $this->leastPermission = $_;
        return $this;
    }
}