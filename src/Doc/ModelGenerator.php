<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Front\Controllers\Beans\Table;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\Library\Utils;

class ModelGenerator implements IDocGenerator
{
    public string $content;

    /**
     * @param Table $tableInfo
     */
    public function __construct(public Table $tableInfo){

    }

    public function generate():FileStore{
        $class = Utils::camelize($this->tableInfo->table);

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace App\Models;";
        $headerLines[] = "";
        $useLines[] = "use Illuminate\Database\Eloquent\Model;";
        $lines[] = "";
        $lines[] = "class {$class} extends Model";
        $lines[] = "{";
        $propertyLines = $this->propertyLines();

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
    private function propertyLines(){
        $content = [];
        $tab = $this->tab();
        $content[] =  $tab."protected \$table = '{$this->tableInfo->table}'";

        foreach ($this->tableInfo->columns as $column){
            if($column->is_primary){
                $content[] =  $tab."protected \$primaryKey = '{$column->column}';";
                if(!str_contains($column->type,'int')){
                    $content[] = "protected \$keyType = 'string';";
                    $content[] = "public \$incrementing = false;";
                }
            }
        }

        return $content;
    }

    private function tab(){
        return str_repeat(" ",4);
    }

}
