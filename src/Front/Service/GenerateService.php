<?php

namespace LaravelNemo\Front\Controllers\Service;

use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\JsonModelReq;
use LaravelNemo\Front\Controllers\Beans\JsonNode;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Doc\BeanGenerator;
use LaravelNemo\Library\Utils;

class GenerateService
{

    /**
     * @param  $nodes JsonNode[]
     * @return array
     */
    public function genJsonModel(array $nodes,string $namespace,string $className ){
        $classList[] = $this->parseJsonChildren($nodes,$namespace,$className);

        foreach ($nodes as $node){
            if($node->children){
                //数组的非对象Item子节点不应该被解析
                $children = $node->children;
                if($node->type==='array'){
                    if($node->array_object){
                        $children = $node->array_object;
                    }else{
                        continue;
                    }
                }

                $classList = [...$classList,...$this->genJsonModel($children,$namespace,Utils::camelize($node->name))];

            }
        }
        $classList = array_filter($classList,function($val){
           return !empty($val);
        });
        return $classList??[];
    }

    /**
     * @param JsonNode[] $nodes
     * @param string $namespace
     * @param string $className
     * @return ClassBean
     */
    public function parseJsonChildren(array $nodes,string $namespace,string $className){
        $classInfo = ClassBean::fromItem(['className'=>$className,'namespace'=>$namespace,'propertyList'=>[]]);
        foreach ($nodes as $node){
            $classInfo->propertyList[] = PropertyInfo::jsonGen($node,$namespace);
        }
        return $classInfo;
    }

    /**
     * @param JsonNode $node
     * @return bool
     */
    public function canSkip($node){
        if(preg_match('/^(\[\]|int|bool|float|string|array).*?/',(string)$node->array_type)){
            return true;
        }else{
            return false;
        }
    }
}
