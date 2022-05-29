<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\Columns;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class QueryPaginateBeanGenerator extends BaseCodeGenerator
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
        $tab = $this->tab();
        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace {$this->namespace};";
        $headerLines[] = "";
        $useLines[] = "use LaravelNemo\AttributeClass\Doc;";
        $useLines[] = "use LaravelNemo\Library\PaginateRequest;";
        $lines[] = "";
        $lines[] = "class {$class} extends PaginateRequest";
        $lines[] = "{";
        $propertyLines = $this->getPropertyLines();
        $lines = [...$headerLines,...$useLines,...$lines,...$propertyLines];
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        $ruleLines = $this->ruleLines();

        $lines = [...$lines,...$ruleLines];
        $lines[] = '}';

        $content = implode(PHP_EOL, $lines);
        $this->content = $content;
        return new FileStore($content, 'php');
    }


    private function getPropertyLines(){
        $tab = $this->tab();
        $lines[] = "{$tab}//todo your code....";
        $lines[] = "{$tab}//#[Doc('name')]";
        $lines[] = "{$tab}//public string \$name;";
        return $lines;
    }


    private function ruleLines(){
        $tab = $this->tab();
        $line[] = "{$tab}public function rules()";
        $line[] = "{$tab}{";
        $line[] = "{$tab}{$tab}return [";
        $line[] = "{$tab}{$tab}{$tab}'page'=>'integer',";
        $line[] = "{$tab}{$tab}{$tab}'perPage'=>'integer',";
        $line[] = "{$tab}{$tab}];";
        $line[] = "{$tab}}";
        $line[] = '';
        return $line;
    }

    private function tab(){
        return str_repeat(" ",4);
    }
}
