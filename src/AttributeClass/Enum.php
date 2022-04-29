<?php

namespace LaravelNemo\AttributeClass;

#[\Attribute]
class Enum
{
    /**
     * @param string $enumClass 属性关联的枚举类
     */
    public function __construct(
        public string $enumClass
    )
    {

    }


    public function getEnumInfo(){
        if(is_callable([$this->enumClass,'labelData'])){
            $labelData = call_user_func([$this->enumClass,'labelData']);
        }
        return [
            'name'=>$this->enumClass,
            'labelData'=>$labelData??[]
        ];
    }
}
