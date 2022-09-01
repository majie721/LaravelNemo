<?php

namespace LaravelNemo\Library;


class CommonResp
{
    /** @var int 状态编号, code = 0表示成功 */
    public int $code;

    /** @var  mixed 响应数据 */
    public  mixed $data;

    /** @var string 消息 */
    public string $message;

    /** @var string 响应id */
    public string $request_id;

    public array $debug = [];

    /** @var int 时间 */
    public int $timestamp;
}
