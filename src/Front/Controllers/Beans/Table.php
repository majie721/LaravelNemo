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

    #[Doc('bean namespance')]
    public string $beanNamespace = 'App\Http\Admin\Beans\Menu';

    #[Doc('列表查询条件字段')]
    #[ArrayInfo('string')]
    public array  $queryColumns = ["*"];

    /** @var MethodInfo[]  */
    #[Doc('需要添加的方法')]
    #[ArrayInfo(MethodInfo::class)]
    public array $methods = [];



    protected function afterFill()
    {
        $this->methods = array_filter($this->methods,function ($val){
            return !!$val->selected;
        });
    }

}
