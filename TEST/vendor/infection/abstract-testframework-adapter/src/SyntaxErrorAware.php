<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework;

interface SyntaxErrorAware
{
    public function isSyntaxError(string $output);
}
