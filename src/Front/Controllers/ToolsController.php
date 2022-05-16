<?php

namespace LaravelNemo\Front\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use LaravelNemo\Doc\EntityGenerator;
use LaravelNemo\Doc\ModelGenerator;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\JsonModelReq;
use LaravelNemo\Front\Controllers\Beans\TableReq;
use LaravelNemo\Front\Service\GenerateService;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Doc\BeanGenerator;
use LaravelNemo\Doc\EldGenerator;
use function Composer\Autoload\includeFile;

class ToolsController extends BaseController
{



    public function __construct(public GenerateService $service)
    {
    }

    public function index(){
        return view('nemoView::index');
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

    public function tables(){

        $res = Order::find(1)->get()->toArray();
        dd($res);

        $res = DB::select("select  TABLE_NAME `table`,COLUMN_NAME  `column`,COLUMN_DEFAULT  `default`,IF(IS_NULLABLE='YES',1,0) nullable,COLUMN_TYPE type,if(COLUMN_KEY = 'PRI',1,0) is_primary,COLUMN_COMMENT comment   from information_schema.COLUMNS  where table_schema  = ?",['test_2']);
        $res =  collect($res)->groupBy('table')->toArray();
        $list = [];
        foreach ($res as $table=>$data){
            $list[] = [
                'table'=>$table,
                'columns'=>$data
            ];
        }
        return $this->response($list);
    }


    public function modelGen(TableReq $data){
        $files = [];
        foreach ($data->list as $item){
            $path =  storage_path('Models');
            (new ModelGenerator($item))->generate()->store($path,true);
            $files['Models'][] = $path;

            $path =  storage_path('Entities');
            (new EntityGenerator($item))->generate()->store($path,true);
            $files['Entities'][] = $path;
        }

        return $this->response(['files'=>$files]);
    }


}
