<?php
/**
 * Copyright © 2017 Maks Rafalko
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
    private $executableFinder;
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
        AbstractExecutableFinder $executableFinder,
        InitialConfigBuilder $initialConfigBuilder,
        MutationConfigBuilder $mutationConfigBuilder,
        CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        VersionParser $versionParser
    ) {
        $this->executableFinder = $executableFinder;
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
     *
     * @return string
     */
    public function getExecutableCommandLine(string $configPath): string
    {
        return sprintf(
            '%s %s',
            $this->executableFinder->find(),
            $this->argumentsAndOptionsBuilder->build($configPath)
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
                $this->executableFinder->find(),
                '--version'
            )
        );

        $process->mustRun();
        $versionOutput = $process->getOutput();

        return $this->versionParser->parse($versionOutput);
    }
}
