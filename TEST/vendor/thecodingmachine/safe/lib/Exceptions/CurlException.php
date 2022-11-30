<?php

namespace _HumbugBox9658796bb9f0\Safe\Exceptions;

class CurlException extends \Exception implements SafeExceptionInterface
{
    public static function createFromPhpError($ch) : self
    {
        return new self(\curl_error($ch), \curl_errno($ch));
    }
}
