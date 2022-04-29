<?php

namespace LaravelNemo\AttributeClass;

#[\Attribute]
class Doc
{

    /**
     * @param string $doc 属性注释
     * @param bool $option api使用是否可选
     */
    public function __construct(
        public string $doc,
        public bool   $option = false,
    )
    {

    }


    public function getData(){
        return [
            'doc' => $this->doc,
            'option' => $this->option
        ];
    }

    public function getDoc()
    {
        return $this->doc??'';
    }

    public function getOption()
    {
        return $this->option;
    }
}
