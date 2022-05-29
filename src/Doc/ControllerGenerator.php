<?php

namespace LaravelNemo\Doc;

use Illuminate\Support\Facades\File;
use LaravelNemo\Front\Controllers\Beans\MethodInfo;
use LaravelNemo\Front\Controllers\Beans\Table;

class ControllerGenerator extends BaseCodeGenerator
{

    public function __construct(public Table $tableInfo,
                                public MethodInfo $methodInfo,
                                public string $actionName,
                                public string $path)
    {

    }

    public function generate(): FileStore
    {
        $class = $this->methodInfo->getControllerName();
        $namespace = $this->methodInfo->getControllerNamespace();
        $serviceName = $this->methodInfo->getServiceName();
        $varName = lcfirst($serviceName);
        $serviceNamespace = $this->methodInfo->getServiceNamespace();

        $res = File::exists($this->path);
        if($res){ //已有文件
            $originalContent =  trim(file_get_contents($this->path));
            $lines = explode(PHP_EOL,$originalContent);
            array_pop($lines); //先去掉最后一行

            $hasService = false; //controller 是否已经注入service
            foreach ($lines as $line){
                if(str_starts_with($line,"use {$this->methodInfo->serviceName}")){
                    $hasService = true;
                    break;
                }
            }

            if(false === $hasService || $this->responseClass ){ //如果没有注入 php文件要加入use语句, 构造函数注入service
                $flag = 0;
                $postion = 0;
                foreach ($lines as $index =>  $line){
                    if(str_starts_with($line,"use ")){
                        $flag =1;
                    }else{
                        if(1 == $flag){
                            if(false === $hasService){
                                array_splice($lines,$index,0,"use {$this->methodInfo->serviceName};");
                            }

                            if($this->responseClass){
                                array_splice($lines,$index,0,"use {$this->responseClass};");
                            }

                            break;
                        }
                    }
                }

                if(false === $hasService){
                    foreach ($lines as $index => $line){
                        if(str_contains($line,'public function __construct(')){
                            $offset = strpos($line,'public function __construct(')+28;

                            $lines[$index] =  substr_replace($line,"public {$serviceName} {$varName},",$res,0);
                            break;
                        }
                    }
                }
            }
        }else{
            $lines = [];
            $lines[] = '<?php';
            $lines[] = '';
            $lines[] = "namespace {$namespace};";
            $lines[] = "";
            $lines[] = "";
            $lines[] = "use LaravelNemo\AttributeClass\ApiDoc;";
            $lines[] = "use LaravelNemo\Facades\ApiResponse;";
            $lines[] = "use {$this->methodInfo->serviceName};";
            $this->paramBeanClass && $lines[] = "use {$this->paramBeanClass};";
            $lines[] = "";
            $lines[] = "class {$class} extends BaseController";
            $lines[] = "{";
            $lines[] = "";
            $lines[] = "";
        }
        $codeLines = $this->{"{$this->methodInfo->action}Content"}($varName);
        $lines = [...$lines, ...$codeLines];
        $lines[] = '';
        $lines[] = '}';

        $content = implode(PHP_EOL, $lines);
        return new FileStore($content, 'php');
    }


    public function addContent($varName): array
    {
        return $this->saveContent($varName);
    }

    public function editContent($varName): array
    {
        return $this->saveContent($varName);
    }

    public function saveContent($varName){
        $tab = $this->tab();
        $lines = [];
        $lines[] = "{$tab}#[ApiDoc('{$this->methodInfo->apiMoudel}','{$this->methodInfo->apiName}',)]";
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$bean){";
        $lines[] = "{$tab}{$tab}\$this->{$varName}->{$this->actionName}(\$bean);";
        $lines[] = "{$tab}{$tab}return ApiResponse::success();";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function deleteContent($varName): array
    {
        $tab = $this->tab();
        $lines = [];
        $lines[] = "{$tab}public function {$this->actionName}(int \$id){";
        $lines[] = "{$tab}{$tab}\$this->{$varName}->delete(\$id);";
        $lines[] = "{$tab}{$tab}return ApiResponse::success();";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function queryPaginateContent($varName): array
    {
        $tab = $this->tab();
        $lines = [];
        $lines[] = "{$tab}#[ApiDoc('{$this->methodInfo->apiMoudel}','{$this->methodInfo->apiName}',$this->responseClassName::class)]";
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$bean){";
        $lines[] = "{$tab}{$tab}\$this->{$varName}->{$this->actionName}(\$bean);";
        $lines[] = "{$tab}{$tab}return ApiResponse::data(\$result);";
        $lines[] = "{$tab}}";
        return $lines;
    }

    public function queryAllContent($varName): array
    {
        $tab = $this->tab();
        $lines = [];
        $lines[] = "{$tab}#[ApiDoc('{$this->methodInfo->apiMoudel}','{$this->methodInfo->apiName}',$this->responseClassName::class)]";
        $lines[] = "{$tab}public function {$this->actionName}({$this->paramBeanName} \$bean){";
        $lines[] = "{$tab}{$tab}\$this->{$varName}->{$this->actionName}(\$bean);";
        $lines[] = "{$tab}{$tab}return ApiResponse::data(\$result);";
        $lines[] = "{$tab}}";
        return $lines;
    }

    private function tab(){
        return str_repeat(" ",4);
    }


}
