<?php

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