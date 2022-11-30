<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Adapter;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use _HumbugBox9658796bb9f0\Infection\Config\ValueProvider\PCOVDirectoryProvider;
use _HumbugBox9658796bb9f0\Infection\TestFramework\CommandLineBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use _HumbugBox9658796bb9f0\Infection\TestFramework\VersionParser;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class PhpUnitAdapterFactory implements TestFrameworkAdapterFactory
{
    public static function create(string $testFrameworkExecutable, string $tmpDir, string $testFrameworkConfigPath, ?string $testFrameworkConfigDir, string $jUnitFilePath, string $projectDir, array $sourceDirectories, bool $skipCoverage, bool $executeOnlyCoveringTestCases = \false, array $filteredSourceFilesToMutate = []) : TestFrameworkAdapter
    {
        Assert::string($testFrameworkConfigDir, 'Config dir is not allowed to be `null` for the Pest adapter');
        $testFrameworkConfigContent = file_get_contents($testFrameworkConfigPath);
        $configManipulator = new XmlConfigurationManipulator(new PathReplacer(new Filesystem(), $testFrameworkConfigDir), $testFrameworkConfigDir);
        return new PhpUnitAdapter($testFrameworkExecutable, $tmpDir, $jUnitFilePath, new PCOVDirectoryProvider(), new InitialConfigBuilder($tmpDir, $testFrameworkConfigContent, $configManipulator, new XmlConfigurationVersionProvider(), $sourceDirectories, $filteredSourceFilesToMutate), new MutationConfigBuilder($tmpDir, $testFrameworkConfigContent, $configManipulator, $projectDir, new JUnitTestCaseSorter()), new ArgumentsAndOptionsBuilder($executeOnlyCoveringTestCases), new VersionParser(), new CommandLineBuilder());
    }
    public static function getAdapterName() : string
    {
        return 'phpunit';
    }
    public static function getExecutableName() : string
    {
        return 'phpunit';
    }
}
