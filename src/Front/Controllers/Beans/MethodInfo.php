<?php

namespace LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\Nemo;

class MethodInfo extends Nemo
{
    /** @var string 方法名称 ['add','edit','delete','queryPaginate','queryAll'] */
    public string $action;

    /** @var string 代码路径 eg:App\Http\Admin */
    public string $path;

    /** @var string 控制器类名 eg:App\Http\Admin\Controllers\RoleController */
    public string $controllerName;

    /** @var string service类名 App\Service\PermissionService*/
    public string $serviceName;

    /** @var string 名称 eg:Menu*/
    public string $name;

    /** @var string api所属模块 */
    public string $apiMoudel='';

    /** @var string api所属模块 */
    public string $apiName='';


    public function getBeanTypeName(){
        return match ($this->action){
          'add'=>ucfirst($this->name)."AddBean",
          'edit'=>ucfirst($this->name)."EditBean",
          'delete'=>'',
          'queryPaginate'=>ucfirst($this->name)."ListBean",
          'queryAll'=>ucfirst($this->name)."QueryBean",
        };
    }

    public function getControllerName(){
        $data = explode("\\",$this->controllerName);
        return end($data);
    }

    public function getServiceName(){
        $data = explode("\\",$this->serviceName);
        return end($data);
    }

    public function getBeanTypeNamespace(){
        //return rtrim( $this->path,"\\".$this->getBeanTypeName());
        return rtrim(str_replace("\\".$this->getBeanTypeName(),'',$this->path) );
    }

    public function getControllerNamespace(){
        return rtrim(str_replace("\\".$this->getControllerName(),'',$this->controllerName) );
    }

    public function getServiceNamespace(){
        return rtrim(str_replace("\\".$this->getServiceName(),'',$this->serviceName) );
    }

}
