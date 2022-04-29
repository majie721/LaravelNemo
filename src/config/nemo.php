<?php

/*
|--------------------------------------------------------------------------
| Namo 配置说明
|--------------------------------------------------------------------------
| 1.route配置用于动态路由和api文档生成,[]
| 'web' => [                 //web 路由模块名称
            'name'=>'web',   //路由模块名称
            'prefix' => '', //路由访问前缀
            'namespace'=>'App\Http\Web\Controllers', //路由控制器对应的命名空间
            'separator'=>'_' //规定前端的pathinfo的目录名称使用蛇形
        ],
|
|'forbidden_actions'=>['methods'] //forbidden_actions里的方法都是禁止访问的方法
|
|
*/


return [
    'route'=>[
        'web' => [
            'name'=>'web',
            'prefix' => '',
            'namespace'=>'App\Http\Web\Controllers',
            'separator'=>'_'
        ],
        'api' => [
            'name'=>'api',
            'prefix' => 'api',
            'namespace'=>'App\Http\Api\Controllers',
            'separator'=>'_'
        ],
    ],
    'forbidden_actions'=>[
        "middleware",
        "getMiddleware",
        "callAction",
        "__call",
        "dispatchNow",
        "dispatchSync",
    ]
];
