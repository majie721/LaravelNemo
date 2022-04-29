<?php

namespace LaravelNemo\Exceptions;

class ValidateException extends \RuntimeException
{

    private array $errors;

    public function __construct(array $errors, string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }


    public function getFirstError(): string
    {
        return $this->errors[0] ?? '';
    }
}
