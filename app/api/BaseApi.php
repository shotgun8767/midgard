<?php

namespace app\api;

/**
 * Class BaseApi
 * @mixin ApiCore
 * @package app\api
 */
class BaseApi
{
    /**
     * Api核心
     * @var ApiCore
     */
    protected $ApiCore;

    /**
     * 当前类名
     * @var string
     */
    protected $class;

    /**
     * BaseApi constructor.
     * @param ApiCore|null $ApiCore
     */
    public function __construct(?ApiCore $ApiCore = null)
    {
        if (!is_null($ApiCore) && $ApiCore instanceof ApiCore)
        {
            $this->ApiCore = $ApiCore;
        }
        $this->class = get_called_class();
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->ApiCore, $method], $args);
    }

    /**
     * 获取ApiCore
     * @return ApiCore|null
     */
    public function ApiCore()
    {
        return clone $this->ApiCore;
    }
}