<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework;

interface TestFrameworkAdapterFactory
{
    public static function create(string $testFrameworkExecutable, string $tmpDir, string $testFrameworkConfigPath, ?string $testFrameworkConfigDir, string $jUnitFilePath, string $projectDir, array $sourceDirectories, bool $skipCoverage) : TestFrameworkAdapter;
    public static function getAdapterName() : string;
    public static function getExecutableName() : string;
}
