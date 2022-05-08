<?php

namespace LaravelNemo\Traits;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;

trait EnumTrait
{
    public static function values():array{
        return array_column(self::cases(), 'value');
    }

    #[([['name'=>'string','value'=>'mixed']])]
    public static function labelData():array{
       $data = [];
       $reflectedObj =  new \ReflectionEnum(self::class);
       if(!$reflectedObj->isBacked()){
            return  $data;
       }

        foreach (self::cases() as $case){
            $data[] = [
               'label'=>$case->label(),
                'value'=>$case->value
           ];
        }

       return $data;
    }


    public function label(): string
    {
        return $this->name;
    }



}