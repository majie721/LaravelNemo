<?php

namespace LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\Nemo;

class TableReq extends Nemo
{
    /** @var Table[]  */
    #[ArrayInfo(Table::class)]
    public array $list;
}