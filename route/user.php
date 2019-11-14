<?php

use app\route\{Route, RestfulRegister};
use srp\component\Param;

$Routes = [
    Route::new('user', 'get', ':method')
        ->setRoute('getOnMethod')
        ->setParam([
            'method' => Param::string()->setRequire(true)
        ]),

    Route::new('picture', 'get', ':method')
        ->setRoute('getOnMethod')
        ->setParam([
            'method' => Param::string()->setRequire(true)
        ]),

    Route::new('album', 'get', ':method')
        ->setRoute('getOnMethod')
        ->setParam([
            'method' => Param::string()->setRequire(true)
        ])
];

RestfulRegister::instance()->groupLoad($Routes);