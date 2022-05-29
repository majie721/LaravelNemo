<?php

namespace LaravelNemo\Doc;

abstract class DocParser
{
    /** @var array 已经解析过的class */
    public static $hasParsed = [];

    /** @var string 参数名称(前端蛇形,php小驼峰) */
    public string $name;

    /** @var string 类型（int,string,bool,float,array,object,object[],null;depth=0时响应仅支持，object 和 null） */
    public string $type;

    /** @var bool 是否标量类型: int,string,bool,float,int[],string[],bool[],float[] */
    public bool $isBuiltin;

    /** @var bool 是否可能为空 */
    public bool $isRequired = false;

    /** @var bool  */
    public bool $hasDefaultValue = false;

    /** @var string|null 默认值 */
    public string|null $defaultValue = null;

    /** @var string 文档 */
    public string $document;

    /** @var bool 是否枚举 */
    public bool $isEnum;

    /** @var string 枚举值 */
    public string $enumData;

    /** @var array 子元素 */
    public array $child;

    /** @var int 元素层级深度 */
    public int $depth;

    /** @var string 对象类名 */
    public string $className = '';

    /** @var bool 是否为查询参数 */
    public bool $isQueryParam = false;


    /**
     * @return string
     * @throws \JsonException
     */
    public function getEnumDesc():string{
        if(!$this->isEnum){
            return '';
        }

        $info = json_decode($this->enumData, true, 512, JSON_THROW_ON_ERROR);
        $arr = [];
        foreach ($info['labelData'] as $datum){
            $arr[] =$datum['label'].":".$datum['value'];
        }
        return implode(',',$arr);
    }

}
