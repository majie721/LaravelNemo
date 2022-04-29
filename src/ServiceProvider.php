<?php

namespace LaravelNemo;

use LaravelNemo\Console\GenerateDocument;

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
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocument::class,
            ]);
        }
    }

}