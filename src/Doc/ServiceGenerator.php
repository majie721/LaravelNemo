<?php

namespace LaravelNemo\Doc;

use Illuminate\Support\Facades\File;
use LaravelNemo\Front\Controllers\Beans\MethodInfo;
use LaravelNemo\Front\Controllers\Beans\Table;

class ServiceGenerator extends BaseCodeGenerator
{

    /**
     * @var string 参数bean的class
     */
    public string $beanClasee = '';

    public function __construct(public Table      $tableInfo,
                                public MethodInfo $methodInfo,
                                public string     $actionName,
                                public string     $path)
    {

    }

    public function generate(): FileStore
    {
        $serviceName = $this->methodInfo->getServiceName();
        $varName = lcfirst($serviceName);
        $serviceNamespace = $this->methodInfo->getServiceNamespace();

        $res = File::exists($this->path);
        if ($res) { //已有文件
            $originalContent = trim(file_get_contents($this->path));
            $lines = explode(PHP_EOL, $originalContent);
            array_pop($lines); //先去掉最后一行

            $hasModel = false; //Model有没有被use过
            foreach ($lines as $line) {
                if (str_starts_with($line, "use {$this->modelClass}")) {
                    $hasService = true;
                    break;
                }
            }

            if (false === $hasModel || $this->responseClass || $this->beanClasee || $this->entityclass) { //Model没有被use过
                $flag = 0;
                $postion = 0;
                foreach ($lines as $index => $line) {
                    if (str_starts_with($line, "use ")) {
                        $flag = 1;
                    } else {
                        if (1 == $flag) {
                            if (false === $hasModel && !str_contains($originalContent,"use {$this->modelClass};")) {

                                array_splice($lines, $index, 0, "use {$this->modelClass};");
                            }

                            if ($this->responseClass) {
                                array_splice($lines, $index, 0, "use {$this->responseClass};");
                            }

                            if ($this->paramBeanClass) {
                                array_splice($lines, $index, 0, "use {$this->paramBeanClass};");
                            }

                            if ($this->entityclass && !str_contains($originalContent, "use {$this->entityclass};")) {
                                array_splice($lines, $index, 0, "use {$this->entityclass};");
                            }

                            break;
                        }
                    }
                }

            }
        } else {
            $lines = [];
            $lines[] = '<?php';
            $lines[] = '';
            $lines[] = "namespace {$serviceNamespace};";
            $lines[] = "";
            $lines[] = "";
            $this->modelClass && $lines[] = "use {$this->modelClass};";
            $this->paramBeanClass && $lines[] = "use {$this->paramBeanClass};";
            $this->responseClass && $lines[] = "use {$this->responseClass};";
            $lines[] = "";
            $lines[] = "class {$serviceName}";
            $lines[] = "{";
            $lines[] = "";
            $lines[] = "";
        }

        if($this->methodInfo->action==='delete'){

        }
        $codeLines = $this->{"{$this->methodInfo->action}Content"}();
        $lines = [...$lines, ...$codeLines];
        $lines[] = '';
        $lines[] = '}';
        $content = implode(PHP_EOL, $lines);
        return new FileStore($content, 'php');

    }


    public function addContent(): array
    {
        $tab = $this->tab();
        $lines[] = "{$tab}/**";
        $lines[] = "{$tab} *@param {$this->paramBeanName} \$bean";
        $lines[] = "{$tab} *@return {$this->modelName}|\Illuminate\Database\Eloquent\Model";
        $lines[] = "{$tab} */";
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$bean){";
        $lines[] = "{$tab}{$tab}//todo ...";
        $lines[] = "{$tab}{$tab}return {$this->modelName}::create(\$bean->toArray());";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function editContent(): array
    {
        $tab = $this->tab();
        $lines[] = "{$tab}/**";
        $lines[] = "{$tab} *@param {$this->paramBeanName} \$bean";
        $lines[] = "{$tab} *@return bool";
        $lines[] = "{$tab} */";
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$bean):bool{";
        $lines[] = "{$tab}{$tab}//todo ...";
        $lines[] = "{$tab}{$tab}return {$this->modelName}::where('id',\$bean->id)->update(\$bean->toArray());";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function deleteContent(): array
    {
       
        $tab = $this->tab();
        $lines[] = "{$tab}/**";
        $lines[] = "{$tab} *@param int \$id";
        $lines[] = "{$tab} *@return bool|null";
        $lines[] = "{$tab} */";
        $lines[] = "{$tab}public function {$this->actionName}(int \$id){";
        $lines[] = "{$tab}{$tab}//todo ...";
        $lines[] = "{$tab}{$tab}return {$this->modelName}::where('id',\$id)->delete();";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function queryPaginateContent(): array
    {
        $tab = $this->tab();
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$params, \$columns = ['*']){";
        $lines[] = "{$tab}{$tab}\$list =  {$this->modelName}::where(static function (\$query) use (\$params) {";
        $lines[] = "{$tab}{$tab}{$tab} //todo eg: \$params->id &&  \$query->where('id', 6);";
        $lines[] = "{$tab}{$tab}})->orderby('id', 'desc')->paginate(\$params->perPage, \$params->columns, \$params->pageName, \$params->page);";
        $lines[] = "{$tab}{$tab}return \$list;";
        $lines[] = "{$tab}}";
        return  $lines;
    }

    public function queryAllContent(): array
    {
        $tab = $this->tab();
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$params, \$columns = ['*']){";
        $lines[] = "{$tab}{$tab}\$list =  {$this->modelName}::where(static function (\$query) use (\$params) {";
        $lines[] = "{$tab}{$tab}{$tab} //todo eg: \$params->id &&  \$query->where('id', 6);";
        $lines[] = "{$tab}{$tab}})->select(\$columns)";
        $lines[] = "{$tab}{$tab}{$tab}->orderby('id', 'desc')";
        $lines[] = "{$tab}{$tab}{$tab}->get()";
        $lines[] = "{$tab}{$tab}{$tab}->toArray();";
        $lines[] = "{$tab}{$tab}if (\$list) {";
        $lines[] = "{$tab}{$tab}{$tab}return {$this->entityName}::fromList(\$list);";
        $lines[] = "{$tab}{$tab}}";
        $lines[] = "{$tab}{$tab}return [];";
        $lines[] = "{$tab}}";
        return  $lines;
    }

    private function tab(){
        return str_repeat(" ",4);
    }


}
