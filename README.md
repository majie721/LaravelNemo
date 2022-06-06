# LaravelNemo

> php8.1以上 & laravel9以上

> LaravelNemo 功能:1.将请求request data转换成预定义对应的对象 
> 2.通过注解快速生成前端开发api文档以及前端请求/响应数据的Typescript数据结构 
> 3.自带数据模型生成工具(根据json或者自定义生成对象模型)
> 4,根据laravel的数据库配置,一键生成laravel model文件以及和数据库表对应的entity模型
> 5 对象属性的可以添加装饰器注解,属性变换


> 数据类型需要继承LaravelNemo\Nemo;
> 自定义枚举类型需要实现 implements \ArrayAccess, 可以引入use EnumArrayAccessTrait,EnumTrait;



##1.安装 
* 1.1 composer require majie/laravel-nemo
* 1.2 使用命令发布配置和前端资源:  php artisan  vendor:publish  --tag=nemo --force
* 1.3 nemo路由配置 config/nemo.php 更改rout对应的命名空间
* 1.4 将laravle的路由改成动态路由,eg /routes/web.php
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

##3. LaravelNemo api接口文档(api接口说明和前端ts请求/响应的数据类型)
php artisan generate:document
##3.1.注解接口说明
 * ArrayInfo注解解析对象数组属性,对象里数组属性会根据ArrayInfo填充,api文档也会识别数组类型(数组为多维数组时,type参数用来补充说明);eg:
 * Doc注解解释说明对象属性的解释,它有第二字段标识字段是否可选 Doc('订单商品信息') 
 ```
  ....
   /** 订单商品信息 */
    #[Doc('订单商品信息')]
    #[ArrayInfo(ProductItem::class)]
    public array $products;
 
 ```
  *Decorator注解 用来当做属性的装饰器,在填充对象时会执行装饰器里的函数
  ```
    #[Doc('姓名')]
    #[Decorator('strtolower')]
    public string $name;
  ```
  
  *Enum注解用来解释枚举数据

 
 *ApiDoc注解用来解释控制器里接口,在生成api文档时会根据ApiDoc生成文档  eg IndexController 控制器Action 
  ``` TestController.php
    /** 订单创建接口 */
    #[ApiDoc("订单",'创建订单',PlatformOrderResp::class)]
    public function create(Order $order){
       // ..todo
    }
 ```
 






 







