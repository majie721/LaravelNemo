<?php

namespace LaravelNemo\Exceptions\App;

class ParamsException extends BaseException
{
    /**
     * @param string $message
     * @param int|null $apiCode
     * @param int|null $httpCode
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "",$apiCode = 500, $httpCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $apiCode, $httpCode, $previous);
    }
}
