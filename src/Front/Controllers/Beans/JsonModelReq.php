<?php

namespace LaravelNemo\Front\Controllers\Beans;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\Doc;
use LaravelNemo\Nemo;

class JsonModelReq extends Nemo
{
    /** @var JsonNode[]  */
    #[ArrayInfo(JsonNode::class)]
    public array $list;

    #[Doc('命名空间',true)]
    public ?string $namespace = "App\\Beans";

    /** @var string|null  */
    #[Doc('类名',true)]
    public ?string $className = 'Bean';


}
