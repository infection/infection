<?php

namespace _HumbugBox9658796bb9f0\Safe\Exceptions;

class JsonException extends \Exception implements SafeExceptionInterface
{
    public static function createFromPhpError() : self
    {
        return new self(\json_last_error_msg(), \json_last_error());
    }
}
