<?php

namespace LaravelNemo;

use LaravelNemo\Console\GenerateDocument;
use function PHPUnit\Framework\fileExists;

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

        $this->publishes([
            __DIR__ . "/config/nemo.php" => config_path('nemo.php')
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocument::class,
            ]);
        }
    }
}
