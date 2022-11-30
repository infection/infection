<?php

namespace _HumbugBox9658796bb9f0\Safe\Exceptions;

class StreamException extends \ErrorException implements SafeExceptionInterface
{
    public static function createFromPhpError() : self
    {
        $error = \error_get_last();
        return new self($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
    }
}
