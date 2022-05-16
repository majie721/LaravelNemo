<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\Columns;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class EntityGenerator implements IDocGenerator
{
    /** @var string  */
    public string $content;

    /**
     * @param Table $tableInfo
     */
    public function __construct(public Table $tableInfo){

    }

    public function generate():FileStore{
        $class = Utils::camelize($this->tableInfo->table);
        $filename = $this->tableInfo->table;

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace App\Entities;";
        $headerLines[] = "";
        $useLines[] = "use LaravelNemo\AttributeClass\Doc;";
        $useLines[] = "use LaravelNemo\Nemo;";
        $lines[] = "";
        $lines[] = "class {$this->classInfo->className}Entity extends Nemo";
        $lines[] = "{";
        $propertyLines = [];
        foreach ($this->tableInfo->columns as $column){
            $propertyContent  = $this->propertyLines($column,$useLines);
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
    private function propertyLines(Columns $column,&$useLines){
        $content = [];
        $tab = $this->tab();
        $phpType = $this->getPhpType($column->type);
        $nullable = !!$column->nullable;
        $nullable && $phpType = "$phpType|null";
        $remark =  $column->nullable? "nullable[yes]":"nullable[no],";
        $remark .=  "type[{$column->type}]";
        if($column->default !==null){
            $remark .=",default['{$column->default}']";
        }
        $content[] = "{$tab}/** @var $phpType {$column->comment}($remark) */";
        $content[] =$column->nullable?"{$tab}#[Doc('{$column->comment}')]":"{$tab}#[Doc('{$column->desc}',false)]";
        $nullable &&  $phpType = "?$phpType";
        $content[] ="{$tab}public {$phpType} \${$column->column};";
        $content[] = '';

        return $content;
    }

    private function tab(){
        return str_repeat(" ",4);
    }


    private function getPhpType(string $type){
        if(str_contains($type,'int')){
            return 'int';
        }

        if(str_contains($type,'float') || str_contains($type,'double')){
            return 'float';
        }

        return 'string';
    }



}
