<?php

namespace _HumbugBoxb47773b41c19\Safe\Exceptions;

class CurlException extends \Exception implements SafeExceptionInterface
{
    public static function createFromPhpError($ch = null) : self
    {
        return new self($ch ? \curl_error($ch) : '', $ch ? \curl_errno($ch) : 0);
    }
}
