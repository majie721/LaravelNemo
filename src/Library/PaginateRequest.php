<?php

namespace LaravelNemo\Library;

use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;

class PaginateRequest extends BaseRequest
{
    #[Doc('page页')]
    public int $page = 1;

    #[Doc('每页数据')]
    public int $perPage = 20;

    #[Doc('字段')]
    #[ArrayInfo('string')]
    public array $columns =['*'];

    #[Doc('pageName')]
    public string $pageName = 'page';



}
