<?php

namespace LaravelNemo\Library;

use LaravelNemo\AttributeClass\Doc;

class PaginateResponse extends BaseResponse
{
    #[Doc('当前页')]
    public int $current_page;

    #[Doc('每页数据')]
    public int $per_page;

    #[Doc('数据总条数')]
    public int $total;

    /** @var array 分页数据 */
    public array $data = [];
}
