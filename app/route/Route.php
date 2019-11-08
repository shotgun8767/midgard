<?php

namespace app\route;

use srp\Route as exRoute;

class Route extends exRoute
{
    /**
     * 校验内容
     * @var array
     */
    protected $validate = [];

    /**
     * 访问最低权限
     * @var null|string
     */
    protected $permission;

    /**
     * 路由地址
     * @var string
     */
    protected $route = '';

    /**
     * 路由模板
     * @var array
     */
    protected $rulePattern = [];

    /**
     * 转化为数组
     * @return array
     */
    public function toArray() : array
    {
        RestfulRegister::instance()->ResponseLoadErrcodes();

        $p = parent::toArray();
        return array_merge($p, [
            'validate' => $this->getValidate(),
            'permission' => $this->getPermission()
        ]);
    }

    /**
     * @return string
     */
    public function getRule() : string
    {
        return ":version/$this->controller" . ($this->rule ? "/$this->rule" : "");
    }

    /**
     * @param array $array
     */
    public function afterParse(array $array)
    {
        # 获取验证
        $param = $array['param']??[];
        $post = $array['post']??[];

        $n = function (array $array) {
            $r = [];
            foreach ($array as $name => $arr) {
                if (isset($arr['validate'])) {
                    $r[$name] = $arr['validate'];
                }
            }

            return $r;
        };

        $this->validate['param'] = $n($param);
        $this->validate['post'] = $n($post);

        # 获取最低访问权限
        $this->permission = $array['permission']??null;

        # 获取路由地址
        $this->route = $array['route']??'';

        # 获取路由模板并进行处理
        $this->rulePattern = $array['rule_pattern']??[];
        $this->rulePattern = array_map(function ($regex) {
            if (strpos($regex, '@') !== false) {
                $sp = explode('@', $regex);
                $regex = '/^[]/';
                foreach ($sp as $item) {
                    $item = trim($item);
                    switch ($item) {
                        case 'id' : $regex = '\d+'; break 2;
                        case 'en' : $item = 'a-zA-Z'; break;
                        case 'l'  : $item = 'a-z'; break;
                        case 'u'  : $item = 'A-Z'; break;
                        case 'n'  : $item = '0-9';break;
                        default : $item = '';
                    }
                    $regex = "/^[$item" . substr($regex, 4);
                }
            }
            return $regex;
        }, $this->rulePattern);
    }

    /**
     * 获取校验内容
     * @return array
     */
    public function getValidate() : array
    {
        return $this->validate;
    }

    /**
     * 获取访问最低权限
     * @return null|string
     */
    public function getPermission() : ?string
    {
        return $this->permission;
    }

    /**
     * 获取路由地址（方法名）
     * @return string
     */
    public function getRoute() : string
    {
        return $this->route;
    }

    /**
     * 获取路有模板
     * @return array
     */
    public function getRulePattern() : array
    {
        return $this->rulePattern;
    }

    /**
     * 设置校验内容
     * @param array $_
     * @return Route
     */
    public function setValidate(array $_) : self
    {
        $this->validate = $_;
        return $this;
    }

    /**
     * 设置访问最低权限
     * @param string|null $_
     * @return Route
     */
    public function setPermission(?string $_) : self
    {
        $this->permission = $_;
        return $this;
    }

    /**
     * 设置路由地址
     * @param string $_
     * @return $this
     */
    public function setRoute(string $_) : self
    {
        $this->route = $_;
        return $this;
    }

    /**
     * 设置路由模板
     * @param array $_
     * @return Route
     */
    public function setRulePattern(array $_) : self
    {
        $this->rulePattern = $_;
        return $this;
    }
}