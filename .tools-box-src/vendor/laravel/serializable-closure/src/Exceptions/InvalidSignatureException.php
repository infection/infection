<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Exceptions;

use Exception;
class InvalidSignatureException extends Exception
{
    public function __construct($message = 'Your serialized closure might have been modified or it\'s unsafe to be unserialized.')
    {
        parent::__construct($message);
    }
}
