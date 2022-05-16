<?php

namespace  LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\Nemo;

class Columns extends Nemo
{
    #[Doc('表名')]
    public string $table;

    #[Doc('字段名')]
    public string $column;

    #[Doc('默认值')]
    public string $default;

    #[Doc('是否可空')]
    public int $nullable;

    #[Doc('数据字段类型')]
    public string $type;

    #[Doc('是否主键')]
    public int $is_primary;

    #[Doc('字段注释')]
    public string $comment;


}
