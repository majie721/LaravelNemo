<?php

namespace LaravelNemo\Exceptions\App;

class HttpNotFoundException extends BaseException
{
    /**
     * @param string $message
     * @param int|null $apiCode
     * @param int|null $httpCode
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "",$apiCode = 404, $httpCode = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $apiCode, $httpCode, $previous);
    }
}
