<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\Columns;
use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class AddBeanGenerator extends BaseCodeGenerator
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
        $useLines[] = "use App\Beans\BaseRequest;";
        $lines[] = "";
        $lines[] = "class {$class} extends BaseRequest";
        $lines[] = "{";
        $propertyLines = [];
        foreach ($this->tableInfo->columns as $column){
            $propertyContent  = $this->propertyLines($column,$useLines);
            if(empty($propertyContent)){
                continue;
            }
            $propertyLines = [...$propertyLines,...$propertyContent];
        }
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

    /**
     * @param $PropertyInfo PropertyInfo
     * @return void
     */
    private function propertyLines(Columns $column,&$useLines){
        if('id' ===$column->column){
            return [];
        }

        $content = [];
        $tab = $this->tab();
        $type = $this->getPhpType($column->type);
        $nullable = !!$column->nullable;
        $phpType =  $nullable?"$type|null":$type;
        $content[] =$column->nullable?"{$tab}#[Doc('{$column->comment}',true)]":"{$tab}#[Doc('{$column->comment}')]";
        $nullable &&  $phpType = "?$type";
        $content[] ="{$tab}public {$phpType} \${$column->column};";
        $content[] = '';

        $rule = [];
        $rule[] = $nullable?'nullable':'required';
        $rule[] = $phpType === 'string'?'string':'numeric';
        preg_match('/.*?char\((\d+)\)/',$column->type,$matches);
        if($matches && $matches[1]){
            $rule[] = "max:$matches[1]";
        }
        $rulestr = implode("|",$rule);
        $this->beanRules[] = "'{$column->column}' => '{$rulestr}',";

        return $content;
    }

    private function ruleLines(){
        $tab = $this->tab();
        $line[] = "{$tab}public function rules(): array";
        $line[] = "{$tab}{";
        $line[] = "{$tab}{$tab}return [";
        foreach ($this->beanRules as $rule){
            $line[] = "{$tab}{$tab}{$tab}{$rule}";
        }
        $line[] = "{$tab}{$tab}];";
        $line[] = "{$tab}}";
        $line[] = '';
        return $line;
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
