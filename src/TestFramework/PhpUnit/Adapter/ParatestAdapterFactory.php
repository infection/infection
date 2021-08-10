<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Adapter;


use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\VersionParser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;
use function Safe\file_get_contents;

/**
 * @internal
 */
final class ParatestAdapterFactory implements TestFrameworkAdapterFactory
{
    /**
     * @param list<string> $filteredSourceFilesToMutate
     */
    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
        bool $executeOnlyCoveringTestCases = false,
        array $filteredSourceFilesToMutate = [],
        ?string $phpUnitExecutable = null
    ): TestFrameworkAdapter {
        Assert::string($testFrameworkConfigDir, 'Config dir is not allowed to be `null` for the Paratest adapter');
        Assert::string($phpUnitExecutable, 'PHPUnit executable must be passed to Paratest adapter');

        $testFrameworkConfigContent = file_get_contents($testFrameworkConfigPath);

        $configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(
                new Filesystem(),
                $testFrameworkConfigDir
            ),
            $testFrameworkConfigDir
        );

        $versionParser = new VersionParser();
        $commandLineBuilder = new CommandLineBuilder();

        $phpUnitVersion = self::retrievePhpUnitVersion($versionParser, $commandLineBuilder, $phpUnitExecutable);

        $phpUnitAdapter = new PhpUnitAdapter(
            $testFrameworkExecutable,
            $tmpDir,
            $jUnitFilePath,
            new PCOVDirectoryProvider(),
            new InitialConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                new XmlConfigurationVersionProvider(),
                $sourceDirectories,
                $filteredSourceFilesToMutate
            ),
            new MutationConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                $projectDir,
                new JUnitTestCaseSorter()
            ),
            new ArgumentsAndOptionsBuilder($executeOnlyCoveringTestCases),
            $versionParser,
            $commandLineBuilder,
            $phpUnitVersion
        );

        return new ParatestAdapter($phpUnitAdapter);
    }

    private static function retrievePhpUnitVersion(VersionParser $versionParser, CommandLineBuilder $commandLineBuilder, string $phpUnitExecutable): string
    {
        $testFrameworkVersionExecutable = $commandLineBuilder->build(
            $phpUnitExecutable,
            [],
            ['--version']
        );

        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();

        return $versionParser->parse($process->getOutput());
    }

    public static function getAdapterName(): string
    {
        return 'paratest';
    }

    public static function getExecutableName(): string
    {
        return 'paratest';
    }
}

