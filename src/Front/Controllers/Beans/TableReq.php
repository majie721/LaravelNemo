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

    #[Doc("Model命名空间")]
    public string $modelNamespace;

    #[Doc("实体命名空间")]
    public string $entityNamespace;
}