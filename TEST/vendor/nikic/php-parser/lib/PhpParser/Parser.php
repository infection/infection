<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

interface Parser
{
    public function parse(string $code, ErrorHandler $errorHandler = null);
}
