<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Library\Utils;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\AttributeClass\Enum;
use LaravelNemo\Exceptions\DocumentPropertyError;
use LaravelNemo\PropertyInfo;
use LaravelNemo\Nemo;

class ResponseParser extends DocParser
{

    public function __construct()
    {
    }

    /**
     * @param string|null $responseAttribute
     * @return ResponseParser|null
     * @throws DocumentPropertyError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function parse(string|null $responseAttribute):?ResponseParser
    {

        if(null === $responseAttribute){
           return null;
        }

        if(!class_exists($responseAttribute)){
            throw new \RuntimeException("响应对象{$responseAttribute}不支持或者不存在");
        }


        $this->child = $this->parserClassType($responseAttribute, 1);

        $this->name = '';
        $this->depth = 0;
        $this->type = '';
        $this->document = '';
        $this->isBuiltin = false;
        $this->isEnum = false;
        $this->enumData = '';
        $this->isRequired = true;
        $this->hasDefaultValue = false;
        $this->className = $responseAttribute;

        return $this;
    }


    /**
     * @param string $className
     * @param int $depth
     * @return ResponseParser[]
     * @throws \JsonException
     * @throws \ReflectionException
     */
    private function parserClassType(string $className, int $depth):array
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("{$this->name}#{$className}不存在");
        }

        if(isset(self::$hasParsed[$className])){ //已经解析过的class直接返回,避免递归死循环
            return self::$hasParsed[$className];
        }else{
            self::$hasParsed[$className] = [];
        }


        $properties = [];
        $instance = (new \ReflectionClass($className))->newInstance(null);
        if ($instance instanceof Nemo) {
            $data = $instance::getPropertiesInfo(true,true);
            if (empty($data)) {
                throw new \RuntimeException("{$className}不能为空对象");
            }

            /**
             * @var  $key string
             * @var  $datum PropertyInfo
             */
            foreach ($data as $key => $datum) {
                if(!$datum->typeName){
                    throw new \RuntimeException("{$className}的{$key}属性未定义");
                }

                if(Utils::isScalar($datum->typeName)){
                    $properties[] = $this->newScalar($datum,$depth);
                    continue;
                }
                if('array' === $datum->typeName){
                    if($datum->isBuiltin){
                        $properties[] =  $this->newScalarArray($datum,$depth);
                    }else{ //对象数组
                        $instance = $this->newObjectArray($datum,$depth)->setClassName($datum->arrayType->class);
                        $instance->child =  $this->parserClassType($datum->arrayType->class,$depth+1);
                        $properties[] = $instance;
                    }
                    continue;
                }

                if(class_exists($datum->typeName)){ //对象
                    $instance = $this->newObject($datum,$depth)->setClassName($datum->typeName);
                    $instance->child =  $this->parserClassType($datum->typeName,$depth+1);
                    $properties[] =   $instance;
                    continue;
                }
                throw new \RuntimeException("{$className}的{$key}属性异常");
            }
            self::$hasParsed[$className] = $properties;
            return  $properties;
        }


        throw new \RuntimeException("{$className} 需要继承Proxy实例");
    }


    /**
     * 填充标量类型
     * @param PropertyInfo $info
     * @param int $depth
     * @return ResponseParser
     * @throws \JsonException
     */
    public function newScalar(PropertyInfo $info,int $depth):ResponseParser
    {
        return $this->new( $info, $depth,'scalar');
    }

    /**
     * 填充标量数组类型(非数组对象)
     * @param PropertyInfo $info
     * @param int $depth
     * @return ResponseParser
     * @throws \JsonException
     */
    public function newScalarArray(PropertyInfo $info,int $depth):ResponseParser
    {
        return $this->new( $info, $depth,'array');
    }

    /**
     * 填充对象
     * @param PropertyInfo $info
     * @param int $depth
     * @return ResponseParser
     * @throws \JsonException
     */
    public function newObject(PropertyInfo $info,int $depth):ResponseParser
    {
        return $this->new( $info, $depth,'object');
    }

    /**
     * 填充对象数组类型
     * @param PropertyInfo $info
     * @param int $depth
     * @return ResponseParser
     * @throws \JsonException
     */
    public function newObjectArray(PropertyInfo $info,int $depth):ResponseParser
    {
        return $this->new( $info, $depth,'object[]');
    }


    /**
     * @param PropertyInfo $info
     * @param int $depth
     * @param string $type scalar,array,object,object[]
     * @return ResponseParser
     * @throws \JsonException
     */
    public function new(PropertyInfo $info,int $depth,string $type):ResponseParser
    {
        $instance = new self();
        $instance->name = $info->name;
        $instance->type = match ($type){
            'scalar'=>$info->typeName,
            'array'=>$info->arrayType->type, //标量数组
            'object'=>'object',
            'object[]'=>$type,
        };
        $instance->document = $info->doc;
        $instance->isEnum = !empty($info->enumInfo);
        $instance->enumData = $instance->isEnum? json_encode($info->enumInfo, JSON_THROW_ON_ERROR) :'';
        $instance->child = [];
        $instance->depth = $depth;
        $instance->isBuiltin = in_array($type, ['scalar', 'array'], true);
        $instance->isRequired = $info->allowsNull===false;
        return $instance;
    }

    /**
     * @param string $className
     * @return $this
     */
    private function setClassName(string $className):self{
        $this->className = $className;
        return $this;
    }

}
