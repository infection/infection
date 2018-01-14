<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;
use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Symfony\Component\Process\Process;

abstract class AbstractTestFrameworkAdapter
{
    /**
     * @var AbstractExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var CommandLineArgumentsAndOptionsBuilder
     */
    private $argumentsAndOptionsBuilder;

    /**
     * @var InitialConfigBuilder
     */
    private $initialConfigBuilder;

    /**
     * @var MutationConfigBuilder
     */
    private $mutationConfigBuilder;

    /**
     * @var VersionParser
     */
    private $versionParser;

    public function __construct(
        AbstractExecutableFinder $phpExecutableFinder,
        InitialConfigBuilder $initialConfigBuilder,
        MutationConfigBuilder $mutationConfigBuilder,
        CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        VersionParser $versionParser
    ) {
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->initialConfigBuilder = $initialConfigBuilder;
        $this->mutationConfigBuilder = $mutationConfigBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
        $this->versionParser = $versionParser;
    }

    abstract public function testsPass(string $output): bool;

    abstract public function getName(): string;

    /**
     * Returns path to the test framework's executable
     * Example:
     *     bin/phpspec [arguments] [--options]
     *     bin/phpunit
     *     vendor/phpunit/phpunit/phpunit
     *
     * @param string $configPath
     * @param string $extraOptions
     * @param bool $includePhpArgs
     *
     * @return string
     */
    public function getExecutableCommandLine(string $configPath, string $extraOptions, bool $includePhpArgs = true): string
    {
        return sprintf(
            '%s %s',
            $this->phpExecutableFinder->find($includePhpArgs),
            $this->argumentsAndOptionsBuilder->build($configPath, $extraOptions)
        );
    }

    public function buildInitialConfigFile(): string
    {
        return $this->initialConfigBuilder->build();
    }

    public function buildMutationConfigFile(Mutant $mutant): string
    {
        return $this->mutationConfigBuilder->build($mutant);
    }

    public function getVersion(): string
    {
        $process = new Process(
            sprintf(
                '%s %s',
                $this->phpExecutableFinder->find(),
                '--version'
            )
        );

        $process->mustRun();
        $versionOutput = $process->getOutput();

        return $this->versionParser->parse($versionOutput);
    }
}
