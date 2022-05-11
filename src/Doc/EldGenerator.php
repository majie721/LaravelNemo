<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Front\Controllers\Beans\ClassBean;
use LaravelNemo\Front\Controllers\Beans\PropertyInfo;
use LaravelNemo\Interface\IDocGenerator;

class EldGenerator implements IDocGenerator
{
    public string $content;

    /**
     * @param ClassBean $classInfo
     */
    public function __construct(public ClassBean $classInfo){

    }

    public function generate($api = false):FileStore{
        if($api){
            $use = 'use Fbg\Api\BaseBean;';
            $extends = 'BaseBean';

        }else{

            $use = 'use App\Http\Beans\BaseRequest;';
            $extends = 'BaseRequest';
        }

        $filename = $this->classInfo->className;

        $lines = [];
        $headerLines[] = '<?php';
        $headerLines[] = '';
        $headerLines[] = "namespace {$this->classInfo->namespace};";
        $headerLines[] = "";
        $useLines[] = $use;
        $lines[] = "";
        $lines[] = "class {$this->classInfo->className} extends {$extends}";
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
        $nullFlg = $propertyInfo->required?'':'?';




        $type = $propertyInfo->type !=='object'?$propertyInfo->type:$propertyInfo->className;
        if($type==='array'){
            if($propertyInfo->class){
                $_arr = explode("\\",$propertyInfo->class);
                array_pop($_arr);
                $namespacePreix =  implode('\\',$_arr);
                $docType = "\\".$namespacePreix."\\".$propertyInfo->arrayType;
               // $docType = $propertyInfo->class.'[]';
            }else{
                $docType = $propertyInfo->arrayType;
            }

        }elseif($propertyInfo->type==='object'){

            $docType = "\\".$propertyInfo->class;
        }else{
            $docType = $propertyInfo->type;
        }
        if($propertyInfo->mock==='0'){
            $remark = '';
        }elseif(strpos($propertyInfo->mock,'0,')===0){
            $remark = substr($propertyInfo->mock,2);
            $remark = "($remark)";
        }else{
            $remark = $propertyInfo->mock?"length=$propertyInfo->mock":'length=40';
            $remark = "($remark)";
        }

        $propertyInfo->desc = str_replace(" ",'',trim($propertyInfo->desc));
        $propertyInfo->name = str_replace(" ",'',trim($propertyInfo->name));
        $remark = str_replace(" ",'',trim($remark));
        $content[] = "{$tab}/** @var {$nullFlg}{$docType} {$propertyInfo->desc} {$remark} */";

        $content[] ="{$tab}public {$nullFlg}{$type} \${$propertyInfo->name};";
        $content[] = '';

        return $content;
    }

    private function tab(){
        return str_repeat(" ",4);
    }

    private function arrayClass(string $type){
        preg_match('/^(\[\]|int|bool|float|string|array).*?/',$type,$maths);
        if($maths){
            return "'$maths[1]'";
        }
        return '';
    }

}
