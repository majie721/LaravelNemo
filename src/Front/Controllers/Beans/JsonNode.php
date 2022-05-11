<?php

namespace LaravelNemo\Front\Controllers\Beans;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Library\Utils;
use LaravelNemo\Nemo;

class JsonNode extends Nemo
{
    /** name  */
    public string $name;

    /** int,float,string,bool,array,object */
    public string $type;

    /**  */
    public ?string $desc=null;

    /** @var   */
    public int $depth;

    /** @var JsonNode[]|null  */
    #[ArrayInfo(JsonNode::class)]
    public ?array $children;

    /** mock */
    public ?string $mock = null;

    /** 数组类型(eg:[],int[],....) */
    public ?string $array_type = null;

    /** @var JsonNode[]|null */
    public ?array $array_object = null;

    /** @var bool  */
    public bool $is_array_item;

    /** @var string  */
    public string $key;

    /** @var int 1 必须,0非必填 */
    public int $required;


    public function afterFill()
    {
       if($this->type === 'array' && !$this->is_array_item){
           if(empty($this->children)){
               $this->array_type = 'array';
           }else{
               $info = $this->getArrayInfo($this->children,Utils::camelize($this->name));
               $this->array_object= $info['array_object'];
               $this->array_type= $info['array_type'];
           }

       }
    }


    /**
     * @param JsonNode[] $children
     * @return array ['array_type'=>'int[][]','array_object'=>[]]
     */
    private function getArrayInfo(array $children,$name=''):array{
        if($children[0]->type!=='array'){
            if($children[0]->type ==='object'){
                $children[0]->name = $name;
                return  [
                    'array_type'=>"{$name}[]",
                    'array_object'=>$children[0]->children,
                ];
            }
            return [
                'array_type'=>"{$children[0]->type}[]",
                'array_object'=>null,
            ];

        }else{
            $info =  $this->getArrayInfo($children[0]->children,$name);
            return [
                'array_type'=>"{$info['array_type']}[]",
                'array_object'=>$info['array_object'],
            ];
        }

    }

}
