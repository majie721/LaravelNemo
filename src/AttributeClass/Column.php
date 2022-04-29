<?php

namespace LaravelNemo\AttributeClass;

#[\Attribute]
class Column
{
    /**
     * @param string $doc 属性注释
     * @param string $enum 属性关联的枚举类
     */
    public function __construct(
        public string $doc,
        public string $enum
    )
    {

    }
}