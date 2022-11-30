<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\ErrorHandler;

use _HumbugBox9658796bb9f0\PhpParser\Error;
use _HumbugBox9658796bb9f0\PhpParser\ErrorHandler;
class Throwing implements ErrorHandler
{
    public function handleError(Error $error)
    {
        throw $error;
    }
}
