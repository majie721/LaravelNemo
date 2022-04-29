<?php

namespace LaravelNemo;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayShapeConst;

class PropertyInfo
{
    /** @var string 属性名称 */
    public string $name;

    /** @var bool 是否有默认值 */
    public bool $hasDefaultValue;

    /** @var mixed 默认值 */
    public mixed $defaultValue;

    /** @var string 属性类型 array,string,foalt,bool,类名 */
    public string $typeName;

    /** @var bool 是否可空 */
    public bool $allowsNull;

    /** @var string  */
    public mixed $arrayType;

    /** @var bool 是否标量类型: int,string,bool,float,int[],string[],bool[],float[]*/
    public bool $isBuiltin;

    /** @var string 文档注释 */
    public string $doc = '';

    /** @var bool api中参数是否可选 */
    public bool $option = false;

    /** @var array 枚举值 */
    #[ArrayShape(ArrayShapeConst::enumInfoArrayShape)]
    public array $enumInfo = [];

    /** @var string 类名 */
    public string $className = '';

//    /** @var int 元素层级深度 */
//    public int $depth;

    /** @var array 装饰器函数 */
    #[ArrayShape([[
        'callback'=>'mixed',
        'args'=>'mixed'
    ]])]
    public array $decorators;
}
