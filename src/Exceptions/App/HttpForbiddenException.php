<?php

namespace LaravelNemo\Exceptions\App;

class HttpForbiddenException extends BaseException
{
    /**
     * @param string $message
     * @param int|null $apiCode
     * @param int|null $httpCode
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $apiCode = 403 , int $httpCode = 403, protected ?\Throwable $previous = null)
    {
        parent::__construct($message, $apiCode,$httpCode, $previous);
    }
}
