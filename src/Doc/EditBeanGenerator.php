<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\Columns;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class EditBeanGenerator extends BaseCodeGenerator
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
    public function __construct(public Table $tableInfo,public string $class,public string $extendsClass,public string $namespace){

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
        $useLines[] = "use LaravelNemo\Library\BaseRequest;";
        $lines[] = "";
        $lines[] = "class {$class} extends {$this->extendsClass}";
        $lines[] = "{";
        $propertyLines[] = "{$tab}#[Doc('id')]";
        $propertyLines[] = "{$tab}public int \$id;";
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



    private function ruleLines(){
        $tab = $this->tab();
        $line[] = "{$tab}public function rules(): array";
        $line[] = "{$tab}{";
        $line[] = "{$tab}{$tab}return [";
        $line[] = "{$tab}{$tab}{$tab}'id'=>'required',";
        $line[] = "{$tab}{$tab}]+parent::messages();";
        $line[] = "{$tab}}";
        $line[] = '';
        return $line;
    }

    private function tab(){
        return str_repeat(" ",4);
    }
}
