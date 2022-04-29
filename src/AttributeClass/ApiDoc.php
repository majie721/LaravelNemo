<?php

namespace LaravelNemo\AttributeClass;
/**
 * 用来生成api文档
 */
#[\Attribute]
class ApiDoc
{


    /**
     * @param string $module 模块名称
     * @param string $name   接口名称
     * @param null|string $response 响应的数据的对象的class名称，【不支持返回数组和标量类型。方便开发后期接口响应中添加字段，前端也只用添加字段不用更改数据结构的类型】
     * @param string $desc 描述
     * @param string $method 请求方式
     * @param int $sort 排序[by desc]
     */
    public function __construct(
       public string $module='',
       public string $name='',
       public string|null $response=null,
       public string $desc='',
       public string $method='POST',
       public int    $sort=100,
    )
    {

    }
}
