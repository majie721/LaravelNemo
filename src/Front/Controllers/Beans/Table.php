<?php

namespace  LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\Nemo;
use LaravelNemo\Front\Controllers\Beans\Columns;

class Table extends Nemo
{
    #[Doc('表名')]
    public string $table;

    /** @var Columns[]  */
    #[ArrayInfo(Columns::class,'Columns[]')]
    #[Doc('字段信息')]
    public array $columns;


}
