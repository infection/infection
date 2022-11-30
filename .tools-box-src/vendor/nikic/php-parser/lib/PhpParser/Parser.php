<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

interface Parser
{
    public function parse(string $code, ErrorHandler $errorHandler = null);
}
