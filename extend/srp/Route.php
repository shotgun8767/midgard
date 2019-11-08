<?php

namespace srp;

use srp\component\Param;
use srp\component\Response;

class Route
{
    /**
     * 所属模块
     * @var string
     */
    protected $controller;

    /**
     * 请求方法
     * @var string
     */
    protected $method;

    /**
     * 路由规则
     * @var string
     */
    protected $rule;

    /**
     * 路由名称
     * @var string
     */
    protected $name = '';

    /**
     * 路由简述
     * @var string
     */
    protected $desc = '';

    /**
     * PARAM请求参数
     * @var array
     */
    protected $param = [];

    /**
     * POST请求参数
     * @var array
     */
    protected $post = [];

    /**
     * 相应描述
     * @var array
     */
    protected $response = [];

    /**
     * Route constructor.
     * @param string $controller
     * @param string $method
     * @param string $rule
     */
    public function __construct(string $controller, string $method, string $rule)
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->rule = $rule;
    }

    /**
     * @param array $array
     * @return Route|null
     */
    public static function parse(array $array) : ?self
    {
        $require = ['controller', 'method', 'rule'];
        if (array_intersect(array_keys($array), $require) != $require) {
            return null;
        }

        $instance = new static($array['controller'], $array['method'], $array['rule']);
        $instance
            ->setName($array['name']??'')
            ->setDesc($array['desc']??'')
            ->setParam($array['param']??[])
            ->setPost($array['post']??[])
            ->setResponse($array['response']??[])
            ->afterParse($array);

        return $instance;
    }

    public function afterParse(array $array){}

    /**
     * 转化为数组
     * @return array
     */
    public function toArray()
    {
        return [
            'controller' => $this->getController(),
            'method' => $this->getMethod(),
            'rule' => $this->getRule(),
            'name' => $this->getName(),
            'desc' => $this->getDesc(),
            'param' => $this->getParam(true),
            'post' => $this->getPost(true),
            'response' => $this->getResponse(true)
        ];
    }

    public function setName(string $_) : self
    {
        $this->name = $_;
        return $this;
    }

    public function setDesc(string $_) : self
    {
        $this->desc = $_;
        return $this;
    }

    public function setParam(array $_) : self
    {
        foreach ($_ as $name => $item) {
            if (is_array($item)) {
                $this->param[$name] = Param::parse($item);
            }
        }
        return $this;
    }

    public function setPost(array $_) : self
    {
        foreach ($_ as $name => $item) {
            if (is_array($item)) {
                $this->post[$name] = Param::parse($item);
            }
        }
        return $this;
    }

    public function setResponse(array $_) : self
    {
        $this->response = $_;
        return $this;
    }

    public function getController() : string
    {
        return $this->controller;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getRule() : string
    {
        return $this->rule;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDesc() : string
    {
        return $this->desc;
    }

    public function getParam(bool $toArray = false) : array
    {
        if ($toArray) {
            $return = [];
            foreach ($this->param as $key => $param) {
                if ($param instanceof Param) {
                    $return[$key] = $param->toArray();
                }
            }
            return $return;
        }

        return $this->param;
    }

    public function getPost(bool $toArray = false) : array
    {
        if ($toArray) {
            $return = [];
            foreach ($this->post as $key => $param) {
                if ($param instanceof Param) {
                    $return[$key] = $param->toArray();
                }
            }
            return $return;
        }

        return $this->post;
    }

    public function getResponse(bool $toArray = false) : array
    {
        $return = $this->response;
        foreach ($this->response as $name => $item) {
            if (is_array($item)) {
                $this->response[$name] = Response::parse($item);
            }

            if ($toArray && $this->response[$name] instanceof Response) {
                $return[$name] = $this->response[$name]->toArray();
            }
        }
        return $return;
    }
}