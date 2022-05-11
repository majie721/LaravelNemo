<?php

namespace LaravelNemo;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\ArrayShapeConst;
use LaravelNemo\AttributeClass\Decorator;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\AttributeClass\Enum;

class AttributeParser
{

    protected array $attributesData = [];

    public function __construct(public \ReflectionProperty $property){
        $this->parseAttributes();
    }


    /**
     * @return $this
     */
    protected function parseAttributes(){
        $list = [];
        $attributes = $this->property->getAttributes();
        foreach ($attributes as $attribute){
            $name = $attribute->getName();
            if($attribute->isRepeated()){
                if(!isset($list[$name])){
                    $list[$name] = [
                        'isRepeated'=>true,
                        'name' => $name,
                    ];
                }
                 $list[$name]['attribute'][] = $attribute;
            }else{
                $list[$name] = [
                    'isRepeated'=>false,
                    'name' => $name,
                    'attribute'=>[$attribute],
                ];
            }

        }
        $this->attributesData =  $list;
        return $this;
    }

    /**
     * 解析数组类型的注解
     * @return ArrayInfo|null
     */
    public function getArrayType(){
        $data =  $this->attributesData[ArrayInfo::class]??'';
        if($data){
            $arguments = $data['attribute'][0]->newInstance();
            return $arguments; //todo 待优化
        }

        return null;
    }

    /**
     * 解析文档注释注解
     * @return mixed|string|void
     */
    public function getDoc(){
        $data =  $this->attributesData[Doc::class]??'';
        if($data){
            return  $data['attribute'][0]->newInstance()->getDoc();
        }
        return '';
    }

    /**
     * 解析文档注释注解(是否可选)
     * @return mixed|string|void
     */
    public function getDocOption(){
        $data =  $this->attributesData[Doc::class]??'';
        if($data){
            return  $data['attribute'][0]->newInstance()->getOption();
        }
        return '';
    }

    /**
     *
     * @return array
     */
    #[ArrayShape([])]
    public function getDecorators(){
        $result = [];
        $data = $this->attributesData[Decorator::class]??[];
        if(!empty($data['attribute'])){
            foreach ($data['attribute'] as $item){
                $result[] = $item->newInstance();
            }
        }

       return $result;
    }

    /**
     *
     * @return array
     */
    #[ArrayShape(ArrayShapeConst::enumInfoArrayShape)]
    public function enumInfo(){
        $data =  $this->attributesData[Enum::class]??'';
        if(!$data){
            return [];
        }

        return $data['attribute'][0]->newInstance()->getEnumInfo();

    }



}
