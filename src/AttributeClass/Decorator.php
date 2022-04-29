<?php

namespace LaravelNemo\AttributeClass;

use LaravelNemo\Exceptions\DocumentPropertyError;
use LaravelNemo\Exceptions\ExceptionConstCode;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

#[\Attribute(\Attribute::IS_REPEATABLE|\Attribute::TARGET_PROPERTY)]
class Decorator
{

    private mixed $callback;

    private mixed $args;

    /**
     * @param callable $callback
     * @param mixed ...$args 回调函数的参数
     */
    public function __construct(string|array $callback,mixed ...$args)
    {
        if(is_callable($callback)){
            $this->callback = $callback;
        }else{

            if(is_string($callback)){
                $message = sprintf("The method %s is not exists ",$callback);
                throw new DocumentPropertyError($message,ExceptionConstCode::DECORATOR_METHOD_NOT_EXISTS);
            }

            [$className,$method] = $callback;

            if(!class_exists($className)){
                $message = sprintf("The method %s is not exists",$className);
                throw new DocumentPropertyError($message,ExceptionConstCode::DECORATOR_CLASS_NOT_EXISTS);
            }


            $res = method_exists($className,$method);
            if(!$res){
                $message = sprintf("The method %s is not in Class %s",$className,$method);
                throw new DocumentPropertyError($message,ExceptionConstCode::DECORATOR_METHOD_NOT_EXISTS);
            }

            $this->callback = [$this->invoke($className),$method];

        }

        $this->args = $args;
    }

    public function call($value){

        return call_user_func($this->callback,$value,...$this->args);
    }


    public function invoke($className){ //todo 实现依赖注入
        return new $className;
    }
}