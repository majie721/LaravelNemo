<?php

namespace LaravelNemo\Exceptions;

class ExceptionConstCode
{
    public const  PROPERTY_TYPE_IS_NULL =  01; //类的属性为null
    public const  PROPERTY_TYPE_IS_UNION_TYPE =  702; //类的属性为联合类型
    public const  PROPERTY_TYPE_IS_INTERSECTION_TYPE =  703; //类的属性为交叉类型
    public const  PROPERTY_TYPE_IS_UNKNOWN_TYPE = 704;//类的属性为未知类型

    public const  DECORATOR_METHOD_NOT_EXISTS = 801;//装饰器方法找不到
    public const  DECORATOR_CLASS_NOT_EXISTS = 802;//装饰器class找不到

    public const VALIDATE_FAILURE = 820;//检验失败


}