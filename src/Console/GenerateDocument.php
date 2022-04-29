<?php

namespace App\Console\Commands;

use LaravelNemo\Doc\ControllerDoc;
use LaravelNemo\Doc\ControllerParser;
use LaravelNemo\Doc\HtmlGenerator;
use LaravelNemo\Doc\MarkdownGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:document {name}';

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

        $module = config('command.generateDocument',[]);
        if(empty($module)){
            return $this->error('模块配置[command.generateDocument.module]为空');
        }

        $choice = array_keys($module);

//        $name = $this->choice(
//            '选择待生成文档的模块',
//            $choice,
//            $choice[0]
//        );

        $name = $choice[0];//todo

        $docDir =  $module[$name]['path']??'';

        if(!is_dir($docDir)){
            return $this->error("配置错误:{$docDir}目录不存在");
        }

        $fileData = File::allFiles($docDir);
        $documents = [];
        foreach ($fileData as $fileInfo){
           $parser = new ControllerParser($fileInfo->getRealPath(),$module[$name]['separator']??"/");
           $document =  $parser->init()->parser();
           $document &&  $documents = [...$documents,...$document];
        }

        $documents = $this->documentsSort($documents);
        $generator = new MarkdownGenerator($documents,"{$name} Api Document");
        $generator->generate()->store(public_path('apidoc')."\\{$name}",true);

        $htmlGenerator = new HtmlGenerator($generator->getContent());
        $htmlGenerator->generate()->store(public_path('apidoc')."\\{$name}",true);

    }


    /**
     * 分组后排序
     * @param array $documents ControllerDoc[]
     * @return array array<key=>ControllerDoc[]>
     */
    private function documentsSort(array $documents):array{
        $groupDocuments = [];
        foreach ($documents as  $document){
            $groupDocuments[$document->module][] = $document;
        }


        foreach ($groupDocuments as &$documentArr){
            $documentArr = $this->multiSort($documentArr,'sort');
        }
        return $groupDocuments;
    }


    /**
     * 多维数组排序:
     * eg. multiSort($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     *
     * @param $data
     * @param $key
     * @return array
     */
    private function multiSort($data,$key): array
    {

        array_multisort(array_column($data, $key), SORT_DESC, $data);
        return  $data;

    }
}
