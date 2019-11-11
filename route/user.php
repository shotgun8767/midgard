<?php

use app\route\{Route, RestfulRegister};
use srp\component\Param;

$Routes = [
    Route::new('user', 'get', ':userId')
        ->setRoute('getInfo')
        //->setRulePattern(['userId' => '@id'])
        ->setParam([
            'userId' => Param::int(11)->setRequire(true)
        ]),

    Route::new('picture', 'get', ':pictureId')
        ->setRoute('getPicture')
        ->setRulePattern(['pictureId' => '@id'])
        ->setParam([
            'pictureId' => Param::int(11)
        ])
];

RestfulRegister::instance()->groupLoad($Routes);