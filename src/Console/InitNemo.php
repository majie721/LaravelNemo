<?php

namespace LaravelNemo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelNemo\Doc\ControllerParser;
use LaravelNemo\Doc\HtmlGenerator;
use LaravelNemo\Doc\MarkdownGenerator;

class InitNemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nemo:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成web api文档';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $routeServiceProviderPath = app_path("Providers".DIRECTORY_SEPARATOR."RouteServiceProvider.php");
        if(!File::isWritable($routeServiceProviderPath)){
            $this->error("{$routeServiceProviderPath}重写失败,请保持文件可被重写");
        }

        $content = file_get_contents(__DIR__."../../NemoRouteServiceProvider.demo");
        file_put_contents($routeServiceProviderPath,$content);
        $this->info("{$routeServiceProviderPath}更新成功");

    }

}
