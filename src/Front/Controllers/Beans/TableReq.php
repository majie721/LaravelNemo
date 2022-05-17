<?php

namespace LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\Nemo;

class TableReq extends Nemo
{
    /** @var Table[]  */
    #[ArrayInfo(Table::class)]
    public array $list;

    #[Doc("Model�����ռ�")]
    public string $modelNamespace;

    #[Doc("ʵ�������ռ�")]
    public string $entityNamespace;
}