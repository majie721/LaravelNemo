<?php

namespace LaravelNemo\Doc;

use Illuminate\Support\Facades\File;
use LaravelNemo\Front\Controllers\Beans\MethodInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Front\Controllers\Beans\TableReq;

class CodeGenerator
{

    public string $beanReqName = '';
    public string $beanReqNamespace = '';
    public string $beanRespName = '';
    public string $beanRespNamespace = '';

    /**
     * @param Table $tableReq
     * @param string $modelName model Name
     * @param string $modelNameSpace model 命名空间
     * @param string $entityPath entity entityClass
     */
    public function __construct(protected Table  $table,
                                protected string $modelName,
                                protected string $modelNameSpace,
                                protected string $entityPath,
    )
    {

    }


    public function create()
    {
        foreach ($this->table->methods as $method) {

            //生成请求参数
            $this->createReqBean($method);

            //生成请求响应参数
            $this->createRespBean($method);

            //生成控制器
            $this->createController($method);

            //生成service
            $this->createService($method);

        }
    }

    public function createReqBean(MethodInfo $method)
    {
        if ('delete' === $method->action) {
            $this->setBeanReq('');
            return null;
        }
        $beanName = $method->getBeanTypeName();
        $beanNamespace = $method->getBeanTypeNamespace();
        $path = storage_path('Beans' . DIRECTORY_SEPARATOR . $beanName);
        if ('add' === $method->action) {
            (new AddBeanGenerator($this->table, $beanName, $beanNamespace))->generate()->store($path, true);
        }

        if ('edit' === $method->action) { //todo
            $extendsClass = "{$method->name}AddBean";
            (new EditBeanGenerator($this->table, $beanName, $extendsClass, $beanNamespace))->generate()->store($path, true);
        }

        if ('queryPaginate' === $method->action) { //todo
            (new QueryPaginateBeanGenerator($this->table, $beanName, $beanNamespace))->generate()->store($path, true);
        }

        if ('queryAll' === $method->action) { //todo
            (new QueryBeanGenerator($this->table, $beanName, $beanNamespace))->generate()->store($path, true);
        }

        $this->setBeanReq($beanNamespace."\\".$beanName);
        return $path;
    }

    public function createRespBean(MethodInfo $method)
    {
        if (in_array($method->action, ['delete', 'add', 'edit'])) {
            $this->setBeanResp('');
            return null;
        }

        $beanNamespace = $method->getBeanTypeNamespace();
        if ('queryPaginate' === $method->action) {
            $beanName = ucfirst($method->name) . "PaginateResp";
            $path = storage_path('Beans' . DIRECTORY_SEPARATOR . $beanName);
            (new QueryPaginateRespGenerator($this->table, $beanName, $beanNamespace))
                ->setEntity($this->entityPath)
                ->generate()
                ->store($path, true);
        }

        if ('queryAll' === $method->action) {
            $beanName = ucfirst($method->name) . "ListResp";
            $path = storage_path('Beans' . DIRECTORY_SEPARATOR . $beanName);
            (new QueryRespGenerator($this->table, $beanName, $beanNamespace))
                ->setEntity($this->entityPath)
                ->generate()
                ->store($path, true);
        }

        $this->setBeanResp($beanNamespace."\\".$beanName );
        return $path;

    }

    public function createController(MethodInfo $method)
    {

        //控制器(可能需要追加写入)
        $controllersName = $method->getControllerName();
        $path = storage_path('Controllers' . DIRECTORY_SEPARATOR . $controllersName);
        $actionName = $this->getActionName($method);
        (new ControllerGenerator($this->table,
            $method,
            lcfirst($actionName),
            "{$path}.php")
        )->setParamBeanClass($this->beanReqNamespace)
            ->setResponseClass($this->beanRespNamespace)
            ->generate()
            ->store($path, true);
    }

    public function createService(MethodInfo $method)
    {
        //service(可能需要追加写入)
        $serviceName = $method->getServiceName();
        $path = storage_path('Service' . DIRECTORY_SEPARATOR . $serviceName);
        $actionName = $this->getActionName($method);
        (new ServiceGenerator($this->table,
            $method,
            lcfirst($actionName),
            "{$path}.php")
        )->setParamBeanClass($this->beanReqNamespace)
            ->setResponseClass($this->beanRespNamespace)
            ->setEntity($this->entityPath)
            ->setModel($this->modelNameSpace . '\\' . $this->modelName)
            ->generate()->store($path, true);

    }

    public function setBeanReq($path)
    {
        $arr = $path? explode("\\", $path):[];
        $this->beanReqName = $path ? end($arr) : '';
        $this->beanReqNamespace = $path ?: '';
        return $this;
    }

    public function setBeanResp($path)
    {
        $arr = $path? explode("\\", $path):[];
        $this->beanRespName = $path ? end($arr) : "";
        $this->beanRespNamespace = $path ?: '';
        return $this;
    }

    private function getActionName(MethodInfo $method)
    {
        $actionName = match ($method->action) {
            'add' => "{$method->name}Create",
            'edit' => "{$method->name}Update",
            'delete' => "{$method->name}Destroy",
            'queryPaginate' => "{$method->name}List",
            'queryAll' => "{$method->name}All",
        };
        return $actionName;
    }

}
