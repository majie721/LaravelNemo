<?php

namespace LaravelNemo\Doc;


use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\AttributeClass\Enum;
use LaravelNemo\Exceptions\DocumentPropertyError;
use LaravelNemo\Library\Utils;
use LaravelNemo\PropertyInfo;
use LaravelNemo\Nemo;

class ParameterParser extends DocParser
{


    public function __construct()
    {
    }


    /**
     * @param \ReflectionParameter $parameter
     * @return $this
     * @throws DocumentPropertyError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function parse(\ReflectionParameter $parameter):self
    {
        $this->name = Utils::uncamelize($parameter->getName());
        return $this->parseType($parameter);
    }


    /**
     * @param \ReflectionParameter $parameter
     * @return $this
     * @throws DocumentPropertyError
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function parseType(\ReflectionParameter $parameter):self
    {
        $reflectionType = $parameter->getType();
        $cameName = Utils::camelize($this->name);
        if (null === $reflectionType) {
            throw new \RuntimeException("参数[{$cameName}]的类型不能为空");
        }

        if ($reflectionType instanceof \ReflectionNamedType) {
            $type = $reflectionType->getName();
            if (Utils::isScalar($type)) {
                return $this->setParseTypeData($type, [], true, $parameter, true,1);
            }

            if ('array' === $type) {//数组可以根据ArrayInfo注解解析
                $attributes = $parameter->getAttributes(ArrayInfo::class);
                /** @var ArrayInfo $arrayInfo */
                $arrayInfo = $attributes[0]->newInstance();
                $type = $arrayInfo->type;
                if (empty($attributes)) {
                    return $this->setParseTypeData('[]', [], true, $parameter,false,0);
                }

                if (!$arrayInfo->isObjectArray()) {
                    return $this->setParseTypeData($type, [], true, $parameter,false,0);
                }

                $child = $this->parserClassType($arrayInfo->class,  1);
                return $this->setParseTypeData($type, $child, false, $parameter,false,0)->setClassName($arrayInfo->class);
            }

            //对象
            $parsData = $this->setParseTypeData($type, [], false, $parameter,false,0)->setClassName($type);
            $parsData->child = $this->parserClassType($type, 1);
            return $parsData;
        }

        throw new \RuntimeException("参数[{$cameName}]的声明类型不支持");
    }


    /**
     *
     * @param string $type
     * @param array $child
     * @param bool $isBuiltin
     * @param \ReflectionParameter $parameter
     * @param bool $isQueryPara
     * @param int|null $depth
     * @return $this
     * @throws \JsonException
     * @throws \ReflectionException
     */
    private function setParseTypeData(string $type, array $child, bool $isBuiltin, \ReflectionParameter $parameter, bool $isQueryPara = false,int $depth=null):self
    {

        $docData = isset($parameter->getAttributes(Doc::class)[0]) ? $parameter->getAttributes(Doc::class)[0]->newInstance() : null;
        $this->child = $child;
        $this->isBuiltin = $isBuiltin;
        $this->type = $type;
        $this->hasDefaultValue = $parameter->isDefaultValueAvailable();
        $this->defaultValue = $this->hasDefaultValue ? $parameter->getDefaultValue() : null;
        $this->document = (string)$docData?->getDoc();
        $this->isEnum = !empty($parameter->getAttributes(Enum::class));
        $this->isQueryParam = $isQueryPara;
        $this->isRequired = !($this->hasDefaultValue || $parameter->allowsNull() || $docData?->getOption());
        //$this->child = [];
        !is_null($depth) && $this->depth = $depth;
        $this->enumData = $this->isEnum ? json_encode($parameter->getAttributes(Enum::class)[0]->newInstance()->getEnumInfo(), JSON_THROW_ON_ERROR) : '';

        return $this;
    }


    /**
     * @param string $className
     * @param int $depth
     * @return array
     * @throws DocumentPropertyError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    private function parserClassType(string $className, int $depth):array
    {
        if (!class_exists($className)) {
            $cameName = Utils::camelize($this->name);
            throw new \RuntimeException("{$cameName}#{$className} 不存在");
        }
        $properties = [];
        $instance = (new \ReflectionClass($className))->newInstance(null);
        if ($instance instanceof Nemo) {
            $data = $instance::getPropertiesInfo(true, true);
            if (empty($data)) {
                throw new \RuntimeException("{$className}不能为空对象");
            }

            /**
             * @var  $key string
             * @var  $datum PropertyInfo
             */
            foreach ($data as $key => $datum) {
                if (!$datum->typeName) {
                    throw new \RuntimeException("{$className}的{$key}属性未定义");
                }

                if (Utils::isScalar($datum->typeName)) {
                    $properties[] = $this->newScalar($datum, $depth);
                    continue;
                }
                if ('array' === $datum->typeName) {
                    if ($datum->isBuiltin) {
                        $properties[] = $this->newScalarArray($datum, $depth);
                        continue;
                    }
                    //对象数组
                    $instance = $this->newObjectArray($datum, $depth);
                    $instance->className = $datum->arrayType;
                    $instance->child = $this->parserClassType($datum->arrayType, $depth + 1);
                    $properties[] = $instance;
                    continue;
                }

                if (class_exists($datum->typeName)) { //对象
                    $instance = $this->newObject($datum, $depth);
                    $instance->className = $datum->typeName;
                    $instance->child = $this->parserClassType($datum->typeName, $depth + 1);
                    $properties[] = $instance;
                    continue;
                }
                throw new \RuntimeException("{$className}的{$key}属性异常");
            }

            return $properties;
        }


        throw new \RuntimeException("{$className} 需要继承Proxy实例");
    }


    /**
     * 填充标量类型
     * @param PropertyInfo $info
     * @param int $depth
     * @return ParameterParser
     * @throws \JsonException
     */
    public function newScalar(PropertyInfo $info, int $depth):ParameterParser
    {
        return $this->new($info, $depth, 'scalar');
    }

    /**
     * 填充标量数组类型(非数组对象)
     * @param PropertyInfo $info
     * @param int $depth
     * @return ParameterParser
     * @throws \JsonException
     */
    public function newScalarArray(PropertyInfo $info, int $depth):ParameterParser
    {
        return $this->new($info, $depth, 'array');
    }

    /**
     * 填充对象
     * @param PropertyInfo $info
     * @param int $depth
     * @return ParameterParser
     * @throws \JsonException
     */
    public function newObject(PropertyInfo $info, int $depth):ParameterParser
    {
        return $this->new($info, $depth, 'object');
    }

    /**
     * 填充对象数组类型
     * @param PropertyInfo $info
     * @param int $depth
     * @return ParameterParser
     * @throws \JsonException
     */
    public function newObjectArray(PropertyInfo $info, int $depth):ParameterParser
    {
        return $this->new($info, $depth, 'object[]');
    }


    /**
     * @param PropertyInfo $info
     * @param int $depth
     * @param string $type scalar,array,object,object[]
     * @return ParameterParser
     * @throws \JsonException
     */
    public function new(PropertyInfo $info, int $depth, string $type):ParameterParser
    {
        $instance = new self();
        $instance->name = Utils::uncamelize($info->name);
        $instance->type = match ($type) {
            'scalar' => $info->typeName,
            'array' => "{$info->arrayType}[]",
            'object' => 'object',
            'object[]' => $type,
        };
        $instance->isBuiltin = in_array($type, ['scalar', 'array'], true);
        $instance->hasDefaultValue = $info->hasDefaultValue;
        $instance->defaultValue = $info->defaultValue;
        $instance->document = $info->doc;
        $instance->isEnum = !empty($info->enumInfo);
        $instance->enumData = $instance->isEnum ? json_encode($info->enumInfo, JSON_THROW_ON_ERROR) : '';
        $instance->child = [];
        $instance->depth = $depth;
        $instance->isRequired = !($instance->hasDefaultValue || $info->allowsNull || $info->option);
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
