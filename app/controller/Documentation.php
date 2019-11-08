<?php

namespace app\controller;

use app\route\RestfulRegister;
use think\facade\Request;

class Documentation
{
    public function index()
    {
        return view('index', config('documentation.temp'));
    }

    public function getControllers()
    {
        $controllers = array_map(function ($m) {
            return ucfirst($m);
        }, RestfulRegister::instance()->getControllers());

        echo json_encode($controllers);
    }

    public function getRoutes()
    {
        $controllerName = strtolower(Request::param('controller'));

        $routes = RestfulRegister::instance()->getRoutes($controllerName);

        echo json_encode($routes);
    }

    public function getRouteDetail()
    {
        $method = strtolower(Request::param('method'));
        $rule = Request::param('rule', '');

        if (!$rule) die;

        echo json_encode(RestfulRegister::instance()->getRouteByRule($method, $rule));
    }
}

