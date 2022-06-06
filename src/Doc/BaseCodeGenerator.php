<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Interface\IDocGenerator;

abstract class BaseCodeGenerator implements IDocGenerator
{

    /** @var string apidoc响应className */
    public string $responseClassName='';

    /** @var string apidoc响应class */
    public string $responseClass='';

    /** @var string 实体的名称 */
    public string $entityName = '';

   /** @var string 实体的class */
    public string $entityclass = '';

    /** @var string 模型的名称 */
    public string $modelName = '';

    /** @var string 模型的class */
    public string $modelClass = '';

    /** @var string 方法的参数名称 */
    public string $paramBeanName = '';

    /** @var string 方法的参数class */
    public string $paramBeanClass = '';


    public function setParamBeanClass(string $path){
        if($path!="\\"){
            $arr = explode("\\", $path);
            $this->paramBeanName = end($arr);
            $this->paramBeanClass = $path;
        }

        return $this;
    }

    public function setEntity($path)
    {
        $arr = explode("\\", $path);
        $this->entityName = end($arr);
        $this->entityclass = $path;
        return $this;
    }

    public function setResponseClass(string $path){
        if($path!="\\"){
            $arr = explode("\\", $path);
            $this->responseClassName = end($arr);
            $this->responseClass = $path;
        }
        return $this;
    }

    public function setModel($path)
    {
        if($path!="\\") {
            $arr  = explode("\\", $path);
            $this->modelName = end($arr);
            $this->modelClass = $path;
        }
        return $this;
    }

}
