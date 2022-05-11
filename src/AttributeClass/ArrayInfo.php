<?php

namespace LaravelNemo\AttributeClass;
use LaravelNemo\Library\Utils;

#[\Attribute]
class ArrayInfo
{
    private bool $objectArray = true;

    /**
     * @param string $class 对象数组时对象的类名,或者是string,bool,float
     * @param string $type 数组类型(eg:[],int[],int[][],ClassName[]...)
     */
    public function __construct(
        public string $class,
        public string $type='',
    )
    {
        if(Utils::isScalar($class)){
            $this->objectArray = false;
            $this->type = $type?:"{$class}[]";
        }else{
            $this->type = $type?:$this->getName($class);
        }
    }


    public function isObjectArray():bool{
        return $this->objectArray;
    }

    /**
     * @return int  根据type后面的[]的个数判断数组的维度
     */
    public function dimensional():int{
        return substr_count($this->type,'[]');
    }

    private function getName($class){
        $arr = explode('\\',$class);
        $name =  end($arr);
        return "{$name}[]";
    }
}
