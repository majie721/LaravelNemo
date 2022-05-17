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
    public function __construct(public Table $tableInfo,public $namespace='namespace App\Entities'){

    }

    public function generate():FileStore{
        $class = Utils::camelize($this->tableInfo->table);
        $filename = $this->tableInfo->table;

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "{$this->namespace};";
        $headerLines[] = "";
        $useLines[] = "use LaravelNemo\AttributeClass\Doc;";
        $useLines[] = "use LaravelNemo\Nemo;";
        $lines[] = "";
        $lines[] = "class {$class}Entity extends Nemo";
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
        $type = $this->getPhpType($column->type);
        $nullable = !!$column->nullable;
        $phpType =  $nullable?"$type|null":$type;
        $remark =  $column->nullable? "nullable[yes]":"nullable[no],";
        $remark .=  "type[{$column->type}]";
        if($column->default !==null){
            $remark .=",default['{$column->default}']";
        }
        $content[] = "{$tab}/** @var $phpType {$column->comment}($remark) */";
        $content[] =$column->nullable?"{$tab}#[Doc('{$column->comment}',true)]":"{$tab}#[Doc('{$column->comment}')]";
        $nullable &&  $phpType = "?$type";
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
