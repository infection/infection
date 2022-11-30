<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
interface CommandLineArgumentsAndOptionsBuilder
{
    public function buildForInitialTestsRun(string $configPath, string $extraOptions) : array;
    public function buildForMutant(string $configPath, string $extraOptions, array $tests) : array;
}
