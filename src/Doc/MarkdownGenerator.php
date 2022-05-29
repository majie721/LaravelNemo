<?php

namespace LaravelNemo\Doc;

use LaravelNemo\Library\Utils;
use LaravelNemo\Interface\IDocGenerator;
use LaravelNemo\PropertyInfo;

class MarkdownGenerator implements IDocGenerator
{

    private string $content;

    private array $classNameDefine = [];

    /**
     * @param  array<string=>ControllerDoc[]> $docs
     * @param string $title
     */
    public function __construct(protected array $docs,protected string $title){

    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    private function getMdHeader():array{
        return [
            '[TOC]',
            '# ' . $this->title,
            '',
        ];
    }

    /**
     * @return FileStore
     * @throws \JsonException
     */
    public function generate():FileStore{
        $lines  = $this->getMdHeader();

        $subjectIndex = 0;
        foreach ($this->docs as $module=>$docArr){
            $subjectIndex++;
            $lines[] = "## {$subjectIndex} {$module}";
            /** @var ControllerDoc $doc */
            $index = 0;
            foreach ($docArr as $doc){
                $index++;
                $apiIndex = "{$subjectIndex}.{$index}";
                $lines[] = "### {$apiIndex} {$doc->title}";
                $lines[] = "- **接口说明：** {$doc->desc}";
                $lines[] = "- **接口地址：** {$doc->uri}";
                $lines[] = "- **请求方式：** {$doc->method}";

                $lines[] = "#### {$apiIndex}.1 Query参数";
                $lines[] = "| 参数名称 | 类型 | 是否必填 | 默认值 | 描述 |";
                $lines[] = "| --- | --- | --- | --- | --- |";
                $paramLines =  $this->getQueryParamLine($doc->requestParam);
                $lines = [...$lines,...$paramLines];

                $lines[] = "#### {$apiIndex}.2 Request Body";
                $lines[] = "| 参数名称 | 类型 | 是否必填 | 默认值 | 描述 |";
                $lines[] = "| --- | --- | --- | --- | --- |";
                $paramLines =  $this->getRequestBodyLine($doc->requestParam);
                $lines = [...$lines,...$paramLines];

                $lines[] = "#### {$apiIndex}.3 Response Body";
                $lines[] = "| 参数名称 | 类型 | 是否必填 | 默认值 | 描述 |";
                $lines[] = "| --- | --- | --- | --- | --- |";
                $paramLines =  $this->getResponseBodyLine([$doc->response]);
                $lines = [...$lines,...$paramLines];

                $bodyParam = $doc->requestBody();
                $lines[] = "#### {$apiIndex}.4 TypeScript 请求结构";
                $lines[] = "```";
                $lines[] = empty($bodyParam)?'{}':$this->getTsTypeDefine($bodyParam);
                $lines[] = "```";


                $lines[] = "#### {$apiIndex}.5 TypeScript 响应结构";
                $lines[] = "```";
                $lines[] = empty($doc->response)?'{}':$this->getTsTypeDefine($doc->response);
                $lines[] = "```";


                $lines[] = "#### {$apiIndex}.6请求示例";
                $lines[] = "```json";
                $lines[] = empty($bodyParam)?'{}':$this->getRequestJson($bodyParam);
                $lines[] = "```";

                $lines[] = "#### {$apiIndex}.7响应示例";
                $lines[] = "```json";
                $lines[] = empty($doc->response)?'{}':$this->getResponseJson($doc->response);
                $lines[] = "```";
            }

        }

        $content = implode(PHP_EOL, $lines);
        $this->content = $content;
        return new FileStore($content, 'md');
    }


    /**
     * @param ParameterParser[]|ResponseParser[] $paramsList
     * @param string|null $filterType queryParam,requestBody
     * @return array
     * @throws \JsonException
     */
    public function getLine(array $paramsList,?string $filterType=null):array{
        $elements = [];
        /** @var ParameterParser $item */
        foreach ($paramsList as $item){
            if(!$item){
                continue;
            }

            if(($filterType === 'queryParam') && !$item->isQueryParam){
                continue;
            }

            if(($filterType === 'requestBody') && $item->isQueryParam){
               continue;
            }
            $name = self::placeholder($item->depth-1).$item->name;
            $type = $item->type;
            $required = $item->isRequired ? 'Yes':'No';
            $default = $item->hasDefaultValue?$item->defaultValue:'';
            $desc = $item->document;
            if($item->isEnum){
                $enumData = json_encode(json_decode($item->enumData, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $desc .= "(枚举：【{$enumData}】)";
            }
            $item->depth >0 && $elements[] = "| $name | $type | $required | $default | $desc |";
            if($item->child){
                $elements = [...$elements,...$this->getLine($item->child,$filterType)];
            }
       }
        return  $elements;
    }

    /**
     * 请求参数内容
     * @param array $paramsList
     * @return array
     * @throws \JsonException
     */
    public function getQueryParamLine(array $paramsList):array{
        return $this->getLine($paramsList,'queryParam');
    }

    /**
     * 请求body内容
     * @param array $paramsList
     * @return array
     * @throws \JsonException
     */
    public function getRequestBodyLine(array $paramsList):array{
        return $this->getLine($paramsList,'requestBody');
    }

    /**
     * 请求响应body内容
     * @param array $paramsList
     * @return array
     * @throws \JsonException
     */
    public function getResponseBodyLine(array $paramsList):array{
        return $this->getLine($paramsList);
    }

    /**
     * 占位符
     * @param int $n
     * @return string
     */
    private static function placeholder(int $n):string{
        if($n>0){
            return self::tab($n)."--";
        }
        return '';
    }

    /**
     * @param $n
     * @return string
     */
    private static function tab($n):string{
       return str_repeat("&nbsp;",$n*2);
    }

    /**
     * 请求body ts 的结构定义
     * @param ParameterParser|ResponseParser $param
     * @return string
     */
    private function getTsTypeDefine(ParameterParser|ResponseParser $param):string{
        if(!$param->className){
            return '';
        }

        if(isset($this->classNameDefine[$param->className])){
            return $this->classNameDefine[$param->className];
        }

        $data = $this->getTsType($param);
        $items[] = $data;
        $this->classNameDefine[$param->className] = $data;

        if($param->child){
            foreach ($param->child as $item){
                $childObjectInterface =$this->getTsTypeDefine($item);
                $childObjectInterface && $items[] =  $childObjectInterface;
            }
        }

        return  implode(PHP_EOL,$items);
    }

    private function getTsType(ParameterParser|ResponseParser $param):string{ //todo 标记ts的undefined 和 null

        $namespace = $param->className;
        $interfaceName = self::toTsInterfaceName($param->className);
        $content[] ="{$namespace} :";
        $content[]= "interface {$interfaceName} {";
        $tab = "  ";
        foreach ($param->child as $prop){
            if($prop->className){
                if($prop->type === 'object'){
                   // $this->getTsTypeDefine($param);

                    $doc = "{$tab}/** {$prop->document}  */" ;
                    $name = Utils::uncamelize($prop->name);
                    $type = self::toTsInterfaceName($prop->className);

                    $content[] =  $doc;
                    $content[]= "{$tab}{$name}: {$type};";
                }

                if($prop->type ==='object[]'){
                    //$this->getTsTypeDefine($prop);
                    $doc = "{$tab}/** {$prop->document}  */" ;
                    $name = Utils::uncamelize($prop->name);
                    $type = self::toTsInterfaceName($prop->className).'[]';
                    $content[] =  $doc;
                    $content[] = "{$tab}{$name}: {$type};";
                }
            }

            if($prop->isBuiltin){
                $enumStr = $prop->isEnum ? "。枚举【".$prop->getEnumDesc()."】":'';
                $doc = "{$tab}/** {$prop->document}{$enumStr} */";
                $name = Utils::uncamelize($prop->name);
                $type = self::transformTsType($prop->type);
                $content[] =  $doc;
                $content[]= "{$tab}{$name}: {$type};";
            }
        }
        $content[] = '}';
        return implode(PHP_EOL,$content);
    }

    /**
     * @param string $parameterParserType
     * @return string
     */
    private static function transformTsType(string $parameterParserType):string{
        $tsReplaceMap = [
            'bool'=>'boolean',
            'float'=>'number',
            'int'=>'number',
        ];

        return strtr($parameterParserType,$tsReplaceMap);
    }

    /**
     * php的类名作为Ts的类名(App\Http\Web\Beans\Demo\Demo获取Demo)
     * @param string $phpClassName
     * @return string|null
     */
    private static function toTsInterfaceName(string $phpClassName):?string{
        $arr=  explode("\\",$phpClassName);
       return  array_pop($arr);
    }

    /**
     * @param ParameterParser $parser
     * @return string
     * @throws \JsonException
     */
    private function getRequestJson(ParameterParser $parser):string{
        $mock = [];
        $this->getJson($parser->className,$mock);
        return json_encode($mock, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @param string $className
     * @param array $mock
     * @return array
     */
    private function getJson(string $className,array &$mock):array{
        if($className && is_callable([$className, 'getPropertiesInfo'])) {
            $properties =  call_user_func([$className,'getPropertiesInfo']);
            /**
             * @var  $key
             * @var  $info PropertyInfo
             */
            foreach ($properties as $keyName=>$info){
                if(Utils::isScalar($info->typeName)){
                    $mock[$keyName] = $info->doc?:'';
                }
                if('array'===$info->typeName){
                    if(!$info->arrayType?->isObjectArray()){
                        $mock[$keyName] = $info->doc?[$info->doc]:[];
                    }else{
                        $mock[$keyName] = [];
                        $mock[$keyName] = [$this->getJson($info->arrayType->class,$mock[$keyName])];
                    }

                }
                if(Utils::isClass($info->typeName)){
                    $mock[$keyName] = [];
                    $mock[$keyName] = $this->getJson($info->className,$mock[$keyName]);
                }
            }
            return $mock;
        }
        return [];
    }

    /**
     * @param ResponseParser $parser
     * @return string
     * @throws \JsonException
     */
    private function getResponseJson(ResponseParser $parser):string{
        $mock = [];
        $this->getJson($parser->className,$mock);

        $response = [
            'code'=>0,
            'message'=>'message',
            'debug'=>[],
            'data'=>$mock,
            'timestamp'=>'timestamp',
        ];
        return json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }





}
