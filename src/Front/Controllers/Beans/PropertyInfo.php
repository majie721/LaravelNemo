<?php

namespace LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\Library\Utils;
use LaravelNemo\Nemo;

class PropertyInfo extends Nemo
{
    public string $name;

    public string $type;

    public string $arrayType;

    public string $desc;

    public string $mock;

    public bool   $required = true;

    /** @var string eg:App\Http\Nemo\Controllers\Beans\ServerOrder; */
    public string $class = '';
    /** @var string ServerOrder */
    public string $className = '';


    public static function jsonGen(JsonNode $node,string $namespace){
        $instance = self::fromItem([]);
        $instance->name = $node->name;
        $instance->type = $node->type;
        $instance->arrayType = $node->array_type?:'';
        $instance->desc = $node->desc?:'';
        $instance->required = !!$node->required;
        $instance->mock = $node->mock?:"";
        $instance->class = self::getJsonType($node, $namespace);
        $instance->className = $instance->class?self::getClassName($node):'';
        return $instance;
    }

    private static function getJsonType(JsonNode $node,string $namespace){
        $classType = '';
        if($node->type==='object' ||  ($node->type==='array' && !preg_match('/^(\[\]|int|bool|float|string|array).*?/',(string)$node->array_type))){
            $classType = self::getClassName($node);
            $classType = "{$namespace}\\{$classType}";
        }

        return $classType;
    }

    private static function getClassName(JsonNode $node){
        return Utils::camelize($node->name);
    }
}
