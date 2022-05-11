<?php

namespace LaravelNemo\Front\Controllers;

use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\JsonModelReq;
use App\Http\Nemo\Service\GenerateService;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Doc\BeanGenerator;
use LaravelNemo\Doc\EldGenerator;
use function Composer\Autoload\includeFile;

class ToolsController extends BaseController
{



    public function __construct(public GenerateService $service)
    {
    }

    public function jsonModel(JsonModelReq $req){

        $rootNode = $req->list[0]??[];
        $files = [];
        if($rootNode && count($rootNode->children)){
            $classList =   $this->service->genJsonModel($rootNode->children,$req->namespace,$req->className);
            $_list = explode('\\',$req->namespace);
            $dir = end($_list);
            /** @var ClassBean $item */
            foreach ($classList as $item){
                $path = storage_path($dir.DIRECTORY_SEPARATOR.$item->className);
                (new BeanGenerator($item))->generate()->store($path,true);

                $path = storage_path($dir.'_2'.DIRECTORY_SEPARATOR.$item->className);
                (new EldGenerator($item))->generate()->store($path,true);

                $files[] = $path;
            }
        }
        return $this->response(['files'=>$files]);
    }



}
