<?php

namespace LaravelNemo\Front\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use LaravelNemo\Doc\AddBeanGenerator;
use LaravelNemo\Doc\CodeGenerator;
use LaravelNemo\Doc\EntityGenerator;
use LaravelNemo\Doc\ModelGenerator;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\JsonModelReq;
use LaravelNemo\Front\Controllers\Beans\TableReq;
use LaravelNemo\Front\Service\GenerateService;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Doc\BeanGenerator;
use LaravelNemo\Doc\EldGenerator;
use LaravelNemo\Library\Utils;
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

    public function tables($table_schema = ''){
        $connections =  config('database.connections');
        $table_schema = $table_schema? $connections[$table_schema]['database']:($connections[config('database.default')]['database']);
        $res = DB::select("select  TABLE_NAME `table`,COLUMN_NAME  `column`,COLUMN_DEFAULT  `default`,IF(IS_NULLABLE='YES',1,0) nullable,COLUMN_TYPE type,if(COLUMN_KEY = 'PRI',1,0) is_primary,COLUMN_COMMENT comment   from information_schema.COLUMNS  where table_schema  = ? order by `table`",[$table_schema]);
        $res =  collect($res)->groupBy('table')->toArray();
        $list = [];
        foreach ($res as $table=>$data){
            $list[] = [
                'table'=>$table,
                'columns'=>$data
            ];
        }
        return $this->response(['list'=>$list,'connections'=>$this->connections(),'default'=>config('database.default')]);
    }


    public function createEntity(TableReq $data){
        $files = [];
        $modelNamespace = $data->modelNamespace;
        $entityNamespace = $data->entityNamespace;

        /** 先清空目录 */
        File::cleanDirectory(storage_path('Controllers'));
        File::cleanDirectory(storage_path('Service'));
        File::cleanDirectory(storage_path('Models'));
        File::cleanDirectory(storage_path('Entities'));
        File::cleanDirectory(storage_path('Beans'));

        foreach ($data->list as $item){
            $class = Utils::camelize($item->table);
            $path =  storage_path('Models'.DIRECTORY_SEPARATOR.$class);
            (new ModelGenerator($item,$class,$modelNamespace))->generate()->store($path,true);
            $files['Models'][] = $path;

            $entityName = $class.'Entity';
            $entityClass = $entityNamespace."\\".$entityName;
            $path =  storage_path('Entities'.DIRECTORY_SEPARATOR.$class."Entity");
            (new EntityGenerator($item,$entityName,$entityNamespace))->generate()->store($path,true);
            $files['Entities'][] = $path;

            if(!empty($item->methods)){
                (new CodeGenerator($item,$class,$modelNamespace,$entityClass))->create();
            }


        }

        return $this->response(['files'=>$files]);
    }


    public function connections(){
        $list = [];
        $connections =  config('database.connections');
        foreach ($connections as $connection=>$info){
            if($info['driver'] ==='mysql'){
                $list[] = [
                    'connection'=>$connection,
                    'name'=>$info['database']
                ];
            }
        }
        return $list;
    }


}
