<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\CommandLine\ArgumentsAndOptionsBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder;
final class PhpSpecAdapterFactory implements TestFrameworkAdapterFactory
{
    public static function create(string $testFrameworkExecutable, string $tmpDir, string $testFrameworkConfigPath, ?string $testFrameworkConfigDir, string $jUnitFilePath, string $projectDir, array $sourceDirectories, bool $skipCoverage) : TestFrameworkAdapter
    {
        return new PhpSpecAdapter($testFrameworkExecutable, new InitialConfigBuilder($tmpDir, $testFrameworkConfigPath, $skipCoverage), new MutationConfigBuilder($tmpDir, $testFrameworkConfigPath, $projectDir), new ArgumentsAndOptionsBuilder(), new VersionParser(), new CommandLineBuilder());
    }
    public static function getAdapterName() : string
    {
        return 'phpspec';
    }
    public static function getExecutableName() : string
    {
        return 'phpspec';
    }
}
