<?php

namespace LaravelNemo;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\Exceptions\DocumentPropertyError;
use LaravelNemo\Exceptions\ExceptionConstCode;

class PropertyParser
{

    /** @var Nemo */
    public Nemo $proxyObj;

    /** @var string 实例化的类名 */
    public string $proxyObjName;

    /** @var array 解析对象信息 */
    public static array $proxyPropertyPoll;

    public function __construct(?Nemo $proxy)
    {
        if($proxy){
            $this->proxyObj = $proxy;
            $this->proxyObjName = get_class($proxy);
        }

    }


    /**
     * @param mixed $originalData
     * @return self
     * @throws DocumentPropertyError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function fillData( mixed $originalData): self
    {
        if(null === $originalData){
            return $this;
        }

        if(is_object($originalData)){
            $waitFillData = json_decode(json_encode($originalData, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }else{
            $waitFillData = $originalData;
        }

        $propertiesInfo =  $this->getProxyPropertyData($this->proxyObjName);
        foreach ($waitFillData as $propertyName => $propertyValue){
            $propertyData = $propertiesInfo[$propertyName]??null;
            if($propertyData){
                if($propertyData->isBuiltin){ //标量直接赋值
                    $this->setPropertyValue($propertyName,$propertyValue,$propertyData);
                }else{
                    if($propertyData->arrayType){ //对象数组
                        if(!is_array($propertyValue) && !$propertyData->allowsNull){
                            throw new \TypeError(sprintf("Cannot assign error type to property %s::$%s",$propertyData->arrayType,$propertyName));
                        }

                       $arrayValues = [];
                       foreach ($propertyValue as $item){
                           $arrayValues[] = $this->recursionFill($propertyData->arrayType,$item);//递归填充数据
                       }
                       $this->setPropertyValue($propertyName,$arrayValues,$propertyData);

                    }else{ //对象
                        if(null === $propertyValue){
                            $this->setPropertyValue($propertyName,$propertyValue,$propertyData);
                        }else{
                             $instance = $this->recursionFill($propertyData->typeName,$propertyValue);//递归填充数据
                             $this->setPropertyValue($propertyName,$instance,$propertyData);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string $className
     * @param bool $parseDoc
     * @return $this
     * @throws DocumentPropertyError|\ReflectionException
     */
    public function parseProxyPropertyData(string $className,bool $parseDoc=false,bool $enumDoc=true): self
    {
        $proxyProperty = self::$proxyPropertyPoll[$className]?? null;
        if(null===$proxyProperty){
            $reflection = new \ReflectionClass($className);
            $properties =  $reflection->getProperties();
            foreach ($properties as $property){
                if($property->isPublic()){

                    $propertyName = $property->getName();

                    $reflectionType =  $property->getType();
                    $this->verifyReflectionNamedType($propertyName,$reflectionType);

                    $propertyInfo = new PropertyInfo();
                    $attributeParser = new AttributeParser($property);
                    $propertyInfo->name = $propertyName;
                    $propertyInfo->hasDefaultValue = $property->hasDefaultValue();
                    $propertyInfo->defaultValue = $property->getDefaultValue();
                    $propertyInfo->allowsNull = $reflectionType->allowsNull();
                    $propertyInfo->typeName = $reflectionType->getName();
                    $propertyInfo->arrayType = $attributeParser->getArrayType();
                    $propertyInfo->isBuiltin = $reflectionType->isBuiltin() && in_array($propertyInfo->arrayType,['','int','string','float','bool','int[]','string[]','float[]','bool[]'],true);
                    $parseDoc && $propertyInfo->doc = $attributeParser->getDoc();
                    $parseDoc && $propertyInfo->option = $attributeParser->getDocOption();
                    $enumDoc  && $propertyInfo->enumInfo = $attributeParser->enumInfo();
                    $propertyInfo->decorators = $attributeParser->getDecorators();
                    self::$proxyPropertyPoll[$className][$propertyName] = $propertyInfo;
                }
            }
        }

        return $this;
    }


    /**
     *
     * @return array 对象的属性信息
     * @throws DocumentPropertyError|\ReflectionException
     */
    #[ArrayShape(['Key'=>PropertyInfo::class])]
    public function getProxyPropertyData(string $className,bool $parseDoc=false,bool $enumDoc=true):array{
        $this->parseProxyPropertyData($className,$parseDoc,$enumDoc);
        return self::$proxyPropertyPoll[$className]??[];
    }


    /**
     * 类的属性类型必须注明,并且只能是ReflectionNamedType,不能是联合类型和交叉类型
     * @param string $propertyName
     * @param \ReflectionType|null $property
     * @return void
     * @throws DocumentPropertyError
     */
    private function verifyReflectionNamedType(string $propertyName, ?\ReflectionType $property = null): void
    {

        if($property instanceof \ReflectionNamedType){
            return;
        }

        if($property === null){
            $message = sprintf("The %s property type of the object[ %s ]  cannot be empty.",$propertyName,$this->proxyObjName);
            throw new DocumentPropertyError($message,ExceptionConstCode::PROPERTY_TYPE_IS_NULL);
        }

        if($property instanceof \ReflectionUnionType){
            $message = sprintf("The %s property type of the object[ %s ] cannot be union type.",$propertyName,$this->proxyObjName);
            throw new DocumentPropertyError($message,ExceptionConstCode::PROPERTY_TYPE_IS_UNION_TYPE);
        }

        if($property instanceof \ReflectionIntersectionType){ //php8.1
            $message = sprintf("The %s property type of the %s object cannot be intersection type.",$propertyName,$this->proxyObjName);
            throw new DocumentPropertyError($message,ExceptionConstCode::PROPERTY_TYPE_IS_INTERSECTION_TYPE);
        }

        $message = sprintf("The %s property type of the object[ %s ] cannot be unknown type.",$propertyName,$this->proxyObjName);
        throw new DocumentPropertyError($message,ExceptionConstCode::PROPERTY_TYPE_IS_UNKNOWN_TYPE);
    }

    /**
     * 设置对象的属性值
     * @param string $property
     * @param mixed $value
     * @param PropertyInfo $propertyInfo
     * @return void
     */
    private function setPropertyValue(string $property, mixed $value,PropertyInfo $propertyInfo): void
    {
        if(!empty($propertyInfo->decorators)){
            foreach ($propertyInfo->decorators as $decorator){
                $value =  $decorator->call($value);
            }
        }

        $this->proxyObj->{$property} = $value;
    }

    /**
     * 递归fill data
     * @param $className
     * @param $data
     * @return mixed
     */
    private function recursionFill($className,$data): mixed
    {
            return new $className($data);
    }


}
