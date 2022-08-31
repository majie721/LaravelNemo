<?php

/*
|--------------------------------------------------------------------------
| Namo 配置说明
|--------------------------------------------------------------------------
| 1.route配置用于动态路由和api文档生成,[]
| 'web' => [                 //web 路由模块名称
            'name'=>'web',   //路由模块名称
            'prefix' => '', //路由访问前缀
             middleware'=>[],//中间件
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
        'nemo' => [
            'name'=>'nemo',
            'prefix' => 'nemo',
            'middleware'=>[],
            'namespace'=>'LaravelNemo\Front\Controllers',
            'separator'=>'_',
        ],
        'web' => [
            'name'=>'web',
            'prefix' => '',
            'middleware'=>[],
            'namespace'=>'App\Http\Web\Controllers',
            'separator'=>'_',
            'path'=>base_path("routes/web.php")
        ],
        'api' => [
            'name'=>'api',
            'prefix' => 'api',
            'middleware'=>[],
            'namespace'=>'App\Http\Api\Controllers',
            'separator'=>'_',
            'path'=>base_path("routes/api.php")
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
