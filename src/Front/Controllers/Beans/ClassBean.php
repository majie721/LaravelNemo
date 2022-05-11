<?php

namespace LaravelNemo\Front\Controllers\Beans;

use LaravelNemo\Nemo;

class ClassBean extends Nemo
{

    public string $className;

    public string $namespace;

    /** @var PropertyInfo[]  */
    public array $propertyList;

    public function classNameWithNamespace(): string
    {
        return "{$this->namespace}\\{$this->className}";
    }


}
