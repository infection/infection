<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\ErrorHandler;

use _HumbugBoxb47773b41c19\PhpParser\Error;
use _HumbugBoxb47773b41c19\PhpParser\ErrorHandler;
class Throwing implements ErrorHandler
{
    public function handleError(Error $error)
    {
        throw $error;
    }
}
