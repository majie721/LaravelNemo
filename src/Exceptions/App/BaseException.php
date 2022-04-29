<?php

namespace LaravelNemo\Exceptions\App;

use Exception;

abstract class BaseException extends Exception
{

    /**
     * @param string $message
     * @param int $apiCode
     * @param int $httpCode
     * @param \Throwable|null $previous
     */
    public function __construct(string                $message = "",
                                protected int         $apiCode = 500,
                                protected int         $httpCode = 500,
                                protected ?\Throwable $previous = null)
    {
        parent::__construct($message, $apiCode, $previous);
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
