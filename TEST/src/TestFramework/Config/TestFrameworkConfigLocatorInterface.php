<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Config;

interface TestFrameworkConfigLocatorInterface
{
    public function locate(string $testFrameworkName, ?string $customDir = null) : string;
}
