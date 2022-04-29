<?php

namespace LaravelNemo\Doc;

use LaravelNemo\AttributeClass\ApiDoc;

class ControllerDoc
{
    /** @var string 控制器方法名称  */
    public string $name;

    /** @var string ApiDoc的模块名称  */
    public string $module;

    /** @var string ApiDoc的接口名称 */
    public string $title;

    /** @var ResponseParser|null response的文档 */
    public ResponseParser|null $response;

    /** @var string  ApiDoc的请求方式 */
    public string $method;

    /** @var string ApiDoc的排序 */
    public string $sort;

    /** @var string 路由 */
    public string $uri;

    /** @var string 接口说明 */
    public string $desc;

    /** @var ParameterParser[] 请求参数 */
    public array $requestParam;


    /**
     * @return array|ParameterParser
     */
    public function requestBody():mixed{
      $data =  array_filter($this->requestParam, static function ($val){
            return !$val->isQueryParam;
        });

      return  $data[0]??[];
    }

}
