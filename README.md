# LaravelNemo

> php8.1以上 & laravel9以上


##1.安装 
* composer require majie/laravel-nemo
* php artisan  vendor:publish  --tag=nemo --force
* nemo路由配置 config/nemo.php 更改rout对应的命名空间
* 将laravle的路由改成动态路由,eg /routes/web.php
```

Route::prefix('')->group(function (){
    $config =  config('nemo.route.web',[]);
    Route::any('{controller}/{action}', static function ($controller, $action)use ($config){
        return \LaravelNemo\Library\Router::dispatchRoute($controller,$action,$config);
    })->where('controller','.*');
});
```


##2. LaravelNemo 工具使用
1.配置好本地网站后 打开localhost/nemo/tools/index
2.JSON Mode可以根据json 数据生成数据模型
3 Table Mode可以更具larval配置的数据库(mysql) 生成Model以及对应的Entity

##2. LaravelNemo artisan







