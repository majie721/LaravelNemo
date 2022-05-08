<?php

namespace LaravelNemo\AttributeClass;
#[\Attribute]
class ArrayInfo
{
    /**
     * @param string $type 数组类型(eg:[],int[],int[][],ClassName[]...)
     * @param string $class 对象数组时对象的类名,eg:App\Beans\Demo
     */
    public function __construct(
        public string $type,
        public string   $class = '',
    )
    {

    }


    public function isObjectArray():bool{
        return !!$this->class;
    }

    /**
     * @return int  根据type后面的[]的个数判断数组的维度
     */
    public function dimensional():int{
        return substr_count($this->type,'[]');
    }
}