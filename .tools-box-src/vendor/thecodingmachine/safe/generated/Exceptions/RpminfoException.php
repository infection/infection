<?php

namespace _HumbugBoxb47773b41c19\Safe\Exceptions;

class RpminfoException extends \ErrorException implements SafeExceptionInterface
{
    public static function createFromPhpError() : self
    {
        $error = \error_get_last();
        return new self($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
    }
}
