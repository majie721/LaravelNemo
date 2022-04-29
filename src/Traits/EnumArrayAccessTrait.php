<?php

namespace LaravelNemo\Traits;

trait EnumArrayAccessTrait
{

    public function offsetGet($val):mixed
    {
        $reflectedObj =  new \ReflectionEnum($this);
        if($reflectedObj->hasMethod($val)){
            return  $this->$val();
        }

        if($reflectedObj->hasProperty($val)){
            return $this->$val;
        }

        return null;
    }

    public function offsetExists($val):bool
    {
        return true;
    }

    public function offsetSet($offset='',$value=''): void
    {
        throw new \RuntimeException("Cannot set a value in Enum.");
    }

    public function offsetUnset($val):void
    {
        throw new \RuntimeException("Cannot unset a value in Enum.");
    }

}