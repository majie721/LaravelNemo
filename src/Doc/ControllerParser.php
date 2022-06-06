<?php

namespace LaravelNemo\Doc;

use Illuminate\Support\Facades\File;
use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayInfo;
use LaravelNemo\AttributeClass\ApiDoc;
use LaravelNemo\Exceptions\DocumentPropertyError;
use LaravelNemo\Library\Utils;


class ControllerParser
{

    private string $className;

    private array $documents = [];

    /**
     * @param string $filePath 文件路径
     * @param string $separator api分割符号
     * @throws \RuntimeException
     */
    public function __construct(public string  $filePath,
                                private string $prefix,
                                private string $separator = '/')
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('文件路径不存在');
        }
    }


    /**
     * @return $this
     * @throws \RuntimeException
     */
    public function init():self
    {
        $this->getClass($this->filePath);
        return $this;
    }

    /**
     * 根据 文件内容解析出类名
     * @param $filePath
     * @return $this
     * @throws \RuntimeException
     */
    public function getClass($filePath):self
    {
        $content = file_get_contents($filePath);
        preg_match("/^<\?php\s+namespace(.*?);[\s\S]+class\s+(.*?Controller)[\s\S]*/", $content, $match);
        $className = trim($match[1]) . '\\' . trim($match[2]);
        if (!class_exists($className)) {
            throw new \RuntimeException("{$filePath}文件解析失败:$className");
        }
        $this->className = $className;
        return $this;
    }

    /**
     * @return ControllerDoc[]
     * @throws \ReflectionException
     */
    public function parser():array
    {
        $reflectClass = new \ReflectionClass($this->className);
        $methods = $reflectClass->getMethods();

        $uriPath = $this->getUri($this->className, $this->separator);

        if (!$uriPath) {
            return [];
        }

        foreach ($methods as $method) {
            if (!$method->isPublic()) { //非公有方法不解析
                continue;
            }

            $apiDocAttribute = $method->getAttributes(ApiDoc::class);//没有ApiDoc注解的不解析
            if (empty($apiDocAttribute)) {
                continue;
            }

            $methodName = $method->getName();
            $attributeData = $apiDocAttribute[0]->newInstance();
            $params = $method->getParameters();


            $paramData =  $this->processParams($params,$methodName,$uri);

            $response  = $this->processResponse($attributeData->response,$methodName);

            $methodName = Utils::uncamelize($methodName);
            $uri = "{$uriPath}/$methodName";
            $document = new ControllerDoc();
            $document->name         = $methodName;
            $document->module       = $attributeData->module;
            $document->title        = $attributeData->name;
            $document->response     = $response;
            $document->method       = $attributeData->method;
            $document->sort         = $attributeData->sort;
            $document->desc         = $attributeData->desc;
            $document->uri          = $uri;
            $document->requestParam = $paramData;
            $this->documents[] = $document;
        }

        return $this->documents;
    }


    /**
     * 获取路径
     * @param string $class
     * @param string $separator
     * @return string
     */
    private function getUri(string $class, string $separator = "/"):string
    {
        preg_match("/.*?Controllers(\S*)\\\(\w+)Controller/", $class, $match);
        if ($match) {
            $path = trim("{$match[1]}\\{$match[2]}", '\\');

            $arr = array_map([Utils::class, 'uncamelize'], explode('\\', $path));
            return $this->prefix."/".implode($separator, $arr);
        }

        return '';
    }

    /**
     * 解析参数
     * @param \ReflectionParameter $parameter
     * @return ParameterParser
     * @throws \JsonException
     * @throws DocumentPropertyError
     * @throws \ReflectionException
     */
    private function parseParam(\ReflectionParameter $parameter):ParameterParser
    {
        return (new ParameterParser())->parse($parameter);
    }


    /**
     * 这里校验路由规则:
     * 1.标量类型视为查询参数 例如:/api/users?name=wwx
     * 2.查询参数暂时不支持数组类型(复杂类型的放入请求的body中)
     * 3.请求body是对象才会被文档解析,纯数组不解析成文档
     * 4.因为body是对象或者数组,控制器的复合类型只会有一个
     * @param \ReflectionParameter[] $refParameters
     * @param string $methodName
     * @return array
     * @throws \RuntimeException
     */
    #[ArrayShape(['queryParams' => "array", 'bodyParams' => "array"])]
    private function checkParam(array $refParameters, string $methodName):array{
        $paramsInfo = [
            'queryParams'=>[],
            'bodyParams'=>[],
        ];

        foreach ($refParameters as $parameter){
            $parameterName = $parameter->getName();
            $reflectionType = $parameter->getType();
            if (null === $reflectionType) {
                throw new \RuntimeException("参数[{$parameterName}]的类型不能为空");
            }

            if($reflectionType instanceof \ReflectionNamedType){
                $type=  $reflectionType->getName();
                if(Utils::isScalar($type)){
                    $paramsInfo['queryParams'][$parameterName] = $type;
                }else{
                    $paramsInfo['bodyParams'][] = $parameterName;
                }
            }else{
                throw new \RuntimeException("Controller{$this->className}下的{$methodName}方法参数[{$parameterName}]的类型异常,定义类型必须是ReflectionNamedType");
            }
        }

        if(count($paramsInfo['bodyParams'])>1){
            throw new \RuntimeException("Controller{$this->className}下的{$methodName}方法中最多只能有一个对象或数组");
        }
        return $paramsInfo;
    }


    /**
     * 参数处理
     * @param \ReflectionParameter[] $params
     * @param string $methodName
     * @param $uri
     * @return array
     * @throws \RuntimeException
     */
    private function processParams(array $params,string $methodName,&$uri):array{
        $info =  $this->checkParam($params,$methodName);

        if(!empty($info['queryParams'])){
            $queryStr = urldecode(http_build_query($info['queryParams']));
            $uri .="?$queryStr";
        }

        $paramData = [];
        foreach ($params as $param) {
            try {
                $paramData[] = $this->parseParam($param);
            } catch (\Throwable $e) {
                throw new \RuntimeException("Controller{$this->className}下的{$methodName}方法解析失败:{$e->getMessage()}");
            }
        }
        return $paramData;
    }


    /**
     * @param string|null $responseAttribute
     * @param string $methodName
     * @return ResponseParser|null
     * @throws \RuntimeException
     */
    private function processResponse(string|null $responseAttribute,string $methodName):?ResponseParser{
        try {
            $responseData = $this->parseResponse($responseAttribute);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Controller{$this->className}::{$methodName}下的{$responseAttribute}响应文档解析失败:{$e->getMessage()}");
        }

        return $responseData;
    }

    /**
     * @param string|null $responseAttribute
     * @return ResponseParser|null
     * @throws \JsonException
     * @throws DocumentPropertyError
     * @throws \ReflectionException
     */
    private function parseResponse(string|null $responseAttribute):?ResponseParser{
        return (new ResponseParser())->parse($responseAttribute);
    }


}
