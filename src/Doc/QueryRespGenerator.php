<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\Columns;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class QueryRespGenerator extends BaseCodeGenerator
{
    /** @var string  */
    public string $content;

    /**
     * @var array bean de rules
     */
    private array $beanRules;

    /**
     * @param Table $tableInfo
     */
    public function __construct(public Table $tableInfo,public string $class,public string $namespace){

    }

    public function generate():FileStore{
        $class = $this->class;

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace {$this->namespace};";
        $headerLines[] = "";
        $useLines[] = "use LaravelNemo\AttributeClass\Doc;";
        $useLines[] = "use LaravelNemo\AttributeClass\ArrayInfo;";
        $useLines[] = "use LaravelNemo\Library\BaseResponse;";
        $useLines[] = "use {$this->entityclass};";
        $lines[] = "";
        $lines[] = "class {$class} extends BaseResponse";
        $lines[] = "{";
        $propertyLines = $this->getPropertyLines();
        $lines = [...$headerLines,...$useLines,...$lines,...$propertyLines];
        $lines[] = '';
        $lines[] = '}';

        $content = implode(PHP_EOL, $lines);
        $this->content = $content;
        return new FileStore($content, 'php');
    }

    private function getPropertyLines(){
        $tab = $this->tab();
        $lines[] = "{$tab}#[Doc('list')]";
        $lines[] = "{$tab}#[ArrayInfo({$this->entityName}::class)]";
        $lines[] = "{$tab}public array \$data;";
        return $lines;
    }



    private function tab(){
        return str_repeat(" ",4);
    }
}
