<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Interface\IDocGenerator;

class BeanGenerator implements IDocGenerator
{
    public string $content;

    /**
     * @param ClassBean $classInfo
     */
    public function __construct(public ClassBean $classInfo){

    }

    public function generate():FileStore{
        $filename = $this->classInfo->className;

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace {$this->classInfo->namespace};";
        $headerLines[] = "";
        $useLines[] = "use LaravelNemo\AttributeClass\ArrayInfo;";
        $useLines[] = "use LaravelNemo\AttributeClass\Doc;";
        $useLines[] = "use LaravelNemo\Nemo;";
        $lines[] = "";
        $lines[] = "class {$this->classInfo->className} extends Nemo";
        $lines[] = "{";
        $propertyLines = [];
        foreach ($this->classInfo->propertyList as $propertyInfo){
            $propertyContent  = $this->propertyLines($propertyInfo,$useLines);
            $propertyLines = [...$propertyLines,...$propertyContent];
        }
        $lines = [...$headerLines,...$useLines,...$lines,...$propertyLines];
        $lines[] = '';
        $lines[] = '}';

        $content = implode(PHP_EOL, $lines);
        $this->content = $content;
        return new FileStore($content, 'php');
    }

    /**
     * @param $PropertyInfo PropertyInfo
     * @return void
     */
    private function propertyLines(PropertyInfo $propertyInfo,&$useLines){
        $content = [];
        $tab = $this->tab();
        if($propertyInfo->arrayType){
            $content[] = "{$tab}/** @var {$propertyInfo->arrayType} {$propertyInfo->desc} */";
            $class = $propertyInfo->className?"{$propertyInfo->className}::class":$this->arrayClass($propertyInfo->arrayType);
            $content[] = "{$tab}#[ArrayInfo($class,'{$propertyInfo->arrayType}')]";
        }

        if($propertyInfo->class){
            $useLines[] = "use {$propertyInfo->class};";
        }

        $content[] =$propertyInfo->required?"{$tab}#[Doc('{$propertyInfo->desc}')]":"{$tab}#[Doc('{$propertyInfo->desc}',false)]";
        $type = $propertyInfo->type !=='object'?$propertyInfo->type:$propertyInfo->className;
        $content[] ="{$tab}public {$type} \${$propertyInfo->name};";
        $content[] = '';

        return $content;
    }

    private function tab(){
        return str_repeat(" ",4);
    }

    private function arrayClass(string $type){
        preg_match('/^(\[\]|int|bool|float|string|array).*?/',$type,$maths);
        if($maths){
            return "'$maths[1]'";
        }
        return '';
    }

}
