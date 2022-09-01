<?php

namespace LaravelNemo;

use LaravelNemo\Console\GenerateDocument;
use LaravelNemo\Console\InitNemo;
use LaravelNemo\Library\ApiResponse;
use function PHPUnit\Framework\fileExists;

class NemoServiceProvider extends \Illuminate\Support\ServiceProvider
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
        ],'laravel-assets');

        $this->publishes([
            __DIR__.'/Front/dist/assets' => public_path('assets'),
        ], 'laravel-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InitNemo::class,
                GenerateDocument::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/Front/route/nemo.php');

        $this->loadViewsFrom(__DIR__.'/Front/dist/', 'nemoView');

        $this->app->singleton('ApiResponse', function () {
            return new ApiResponse();
        });
    }
}
