<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception\Coverage\JUnitTestCaseSorter;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Exception\ParseException;
use _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Yaml;
final class CodeceptionAdapterFactory implements TestFrameworkAdapterFactory
{
    public static function create(string $testFrameworkExecutable, string $tmpDir, string $testFrameworkConfigPath, ?string $testFrameworkConfigDir, string $jUnitFilePath, string $projectDir, array $sourceDirectories, bool $skipCoverage) : TestFrameworkAdapter
    {
        return new CodeceptionAdapter($testFrameworkExecutable, new CommandLineBuilder(), new VersionParser(), new JUnitTestCaseSorter(), new Filesystem(), $jUnitFilePath, $tmpDir, $projectDir, self::parseYaml($testFrameworkConfigPath), $sourceDirectories);
    }
    public static function getAdapterName() : string
    {
        return CodeceptionAdapter::NAME;
    }
    public static function getExecutableName() : string
    {
        return 'codecept';
    }
    private static function parseYaml(string $codeceptionConfigPath) : array
    {
        $codeceptionConfigContent = file_get_contents($codeceptionConfigPath);
        try {
            $codeceptionConfigContentParsed = Yaml::parse($codeceptionConfigContent);
        } catch (ParseException $e) {
            throw CodeceptionConfigParseException::fromPath($codeceptionConfigPath, $e);
        }
        return $codeceptionConfigContentParsed;
    }
}
