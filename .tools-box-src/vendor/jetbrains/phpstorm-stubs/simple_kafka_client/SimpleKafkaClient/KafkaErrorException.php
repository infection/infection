<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\SimpleKafkaClient;

class KafkaErrorException extends Exception
{
    public function __construct(string $message, int $code, string $error_string, bool $isFatal, bool $isRetriable, bool $transactionRequiresAbort)
    {
    }
    public function getErrorString() : string
    {
    }
    public function isFatal() : bool
    {
    }
    public function isRetriable() : bool
    {
    }
    public function transactionRequiresAbort() : bool
    {
    }
}
