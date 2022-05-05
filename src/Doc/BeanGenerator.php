<?php

namespace LaravelNemo\Doc;

use App\Http\Nemo\Controllers\Beans\ClassBean;
use App\Http\Nemo\Controllers\Beans\PropertyInfo;
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
        $useLines[] = "use LaravelNemo\AttributeClass\ArrayShape;";
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
            $class =  $propertyInfo->arrayType === 'object'?$propertyInfo->class:$propertyInfo->arrayType;
            $arrayType = $propertyInfo->arrayType==='array'?'':$propertyInfo->arrayType; //
            $content[] = "{$tab}/** @var {$arrayType}[] */";
            $content[] = "{$tab}#[ArrayShape([{$propertyInfo->className}::class])]";
        }

        if($propertyInfo->class){
            $useLines[] = "use {$propertyInfo->class};";
        }

        $content[] ="{$tab}#[Doc('{$propertyInfo->desc}')]";
        $type = $propertyInfo->type !=='object'?$propertyInfo->type:$propertyInfo->className;
        $content[] ="{$tab}public {$type} \${$propertyInfo->name};";
        $content[] = '';

        return $content;
    }

    private function tab(){
        return str_repeat(" ",4);
    }

}
