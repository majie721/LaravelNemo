<?php

namespace LaravelNemo\Interface;
interface IResponse
{
    /**
     * 设置调试信息
     * @param $debug
     * @return $this
     */
    public function setDebug($debug): self;

    /**
     * 返回成功
     *
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function success(string $msg = 'success'): JsonResponse;

    /**
     * 返回普通数据
     *
     * @param null $data
     * @param string $msg
     * @param int $code
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function data($data = null,string $msg = 'success',int $code = 0,int $httpCode = 200): JsonResponse;

    /**
     * 返回错误
     *
     * @param string|null $msg
     * @param int $code
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function error(string $msg = null, int $code = 400, int $httpCode = 400, $data = null): JsonResponse;


}
