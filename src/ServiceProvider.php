<?php

namespace LaravelNemo;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    /**
     * 服务引导方法
     *
     * @return void
     */
    public function boot(): void
    {
        //发布命令文件到项目的中
        $this->publishes([
            __DIR__ . '/Console/GenerateDocument.php' => app_path("Console\Commands\GenerateDocument.php"),
        ]);
    }

}