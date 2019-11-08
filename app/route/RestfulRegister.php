<?php

namespace app\route;

use sek\BaseException;
use srp\component\Response;
use think\facade\App;
use think\facade\Route as ThinkRoute;
use think\route\RuleItem;

/**
 * Class RestfulRegister
 * @package app\route
 */
class RestfulRegister
{
    const MIDDLEWARE_API = 'app\middleware\Api';
    const MIDDLEWARE_VALIDATE = 'app\middleware\Validate';
    const MIDDLEWARE_TOKEN = 'app\middleware\Token';

    /**
     * @var null|self
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $controllers;

    /**
     * 获取实例
     * @return RestfulRegister
     */
    public static function instance() : self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * RestfulRegister constructor.
     */
    public function __construct(){}

    /**
     * 向Response导入errcodes
     */
    public function ResponseLoadErrcodes() : void
    {
        static $loaded = false;

        if (!$loaded) {
            $erc = &Response::$errcodes;
            $files = scandir(App::getAppPath() . 'exception');
            foreach ($files as $file) {
                $Exception = 'app\exception\\' . strstr($file, '.', true);
                if (class_exists($Exception)) {
                    $Class = new $Exception;
                    if ($Class instanceof BaseException) {
                        $erc = $erc ?
                            $Class->getErrcodes() + $erc :
                            $Class->getErrcodes();
                    }
                }
            }
            $loaded = true;
        }
    }

    /**
     * 批量注册路由
     * @param array|null $array
     */
    public function groupLoad(?array $array) : void
    {
        if (is_null($array)) return;

        foreach ($array as $item) {
            if (is_array($item)) {
                $item = Route::parse($item);
            }

            if ($item instanceof Route) {
                $this->load($item);
            }
        }
    }

    /**
     * 单个路由注册
     * @param Route $Route
     * @return RuleItem
     */
    public function load(Route $Route) : RuleItem
    {
        $controller = $Route->getController();
        $this->controllers[$controller][] = $Route;

        # 注册路由
        $RuleItem = $this->register($Route);

        #设定模板
        $RuleItem->pattern($Route->getRulePattern());

        # 绑定中间件
        $RuleItem->middleware(self::MIDDLEWARE_API, [
            'controller' => $Route->getController(),
            'method' => $Route->getRoute()
        ]);

        $RuleItem->middleware(self::MIDDLEWARE_VALIDATE, [
            'Route' => $Route
        ]);

        if (!is_null($perm = $Route->getPermission())) {
            $RuleItem->middleware(self::MIDDLEWARE_TOKEN, [
                'permission' => $perm
            ]);
        }

        return $RuleItem;
    }

    /**
     * 注册
     * @param Route $Route
     * @return RuleItem
     */
    protected function register(Route $Route)
    {
        $rule   = $Route->getRule();
        $route  = config('api.index_route');
        $method = $Route->getMethod();

        return strtolower($Route->getMethod()) === 'any' ?
            ThinkRoute::any($rule, $route) :
            ThinkRoute::rule($rule, $route, $method);
    }

    /**
     * 获取全部控制器（模块）
     * @return array
     */
    public function getControllers() : array
    {
        return array_keys($this->controllers);
    }

    /**
     * 获取全部路由|指定控制器（模块）下的路由
     * @param string|null $controller
     * @return array
     */
    public function getRoutes(?string $controller = null) : array
    {
        $getAll = function () : array
        {
            $r = [];
            foreach ($this->controllers as $con) {
                $r = array_merge($r, $con);
            }
            return $r;
        };

        if (is_null($controller)) return $getAll();

        $routes = $this->controllers[$controller]??[];
        $r = ['method', 'rule', 'name'];
        foreach ($routes as $key => $route) {
            if ($route instanceof Route) {
                $routes[$key] = array_intersect_key($route->toArray(), array_flip($r));
            }
        }

        return $routes;
    }

    /**
     * @param string|null $method
     * @param string|null $rule
     * @return array
     */
    public function getRouteByRule(?string $method, ?string $rule) : array
    {
        $r = ['desc', 'permission', 'param', 'post', 'response'];
        foreach ($this->controllers as $routes) {
            foreach ($routes as $route) {
                if ($route instanceof Route) {
                    if ($route->getRule() == $rule && $route->getMethod() == $method) {
                        return array_intersect_key($route->toArray(), array_flip($r));
                    }
                }
            }
        }

        return [];
    }
}