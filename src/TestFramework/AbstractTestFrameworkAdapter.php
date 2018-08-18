<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;
use Infection\Finder\Exception\FinderException;
use Infection\Mutant\MutantInterface;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
abstract class AbstractTestFrameworkAdapter
{
    /**
     * @var AbstractExecutableFinder
     */
    private $testFrameworkFinder;

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

    /**
     * @var string[]
     */
    private $cachedPhpPath;

    /**
     * @var bool
     */
    private $cachedIncludedArgs;

    public function __construct(
        AbstractExecutableFinder $testFrameworkFinder,
        InitialConfigBuilder $initialConfigBuilder,
        MutationConfigBuilder $mutationConfigBuilder,
        CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        VersionParser $versionParser
    ) {
        $this->testFrameworkFinder = $testFrameworkFinder;
        $this->initialConfigBuilder = $initialConfigBuilder;
        $this->mutationConfigBuilder = $mutationConfigBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
        $this->versionParser = $versionParser;
    }

    abstract public function testsPass(string $output): bool;

    abstract public function getName(): string;

    /**
     * Returns array of arguments to pass them into the Symfony Process
     *
     * @param string $configPath
     * @param string $extraOptions
     * @param bool $includePhpArgs
     * @param array $phpExtraArgs
     *
     * @return string[]
     */
    public function getCommandLine(
        string $configPath,
        string $extraOptions,
        bool $includePhpArgs = true,
        array $phpExtraArgs = []
    ): array {
        $frameworkPath = $this->testFrameworkFinder->find();
        $frameworkArgs = $this->argumentsAndOptionsBuilder->build($configPath, $extraOptions);

        if (false !== strpos($frameworkPath, '.bat')) {
            return array_merge([$frameworkPath], $frameworkArgs);
        }

        /*
         * That's an empty options list by all means, we need to see it as such
         */
        $phpExtraArgs = array_filter($phpExtraArgs);

        /*
         * Run an executable as it is if we're using a standard CLI and
         * there's a standard interpreter available on PATH.
         *
         * This lets folks use, say, a bash wrapper over phpunit.
         */
        if ('cli' === \PHP_SAPI && empty($phpExtraArgs) && is_executable($frameworkPath) && `command -v php`) {
            return array_merge([$frameworkPath], $frameworkArgs);
        }

        /*
         * In all other cases run it with a chosen PHP interpreter
         */
        $commandLineArgs = array_merge(
            $this->findPhp($includePhpArgs),
            $phpExtraArgs,
            [$frameworkPath],
            $frameworkArgs
        );

        return array_filter($commandLineArgs);
    }

    /**
     * Need to return string for cases when user run phpdbg with -qrr argument.s
     *
     * @param bool $includeArgs
     *
     * @return string[]
     */
    private function findPhp(bool $includeArgs = true): array
    {
        if ($this->cachedPhpPath === null || $this->cachedIncludedArgs !== $includeArgs) {
            $this->cachedIncludedArgs = $includeArgs;
            $phpPath = (new PhpExecutableFinder())->find($includeArgs);

            if ($phpPath === false) {
                throw FinderException::phpExecutableNotFound();
            }

            $this->cachedPhpPath = explode(' ', $phpPath);
        }

        return $this->cachedPhpPath;
    }

    public function buildInitialConfigFile(): string
    {
        return $this->initialConfigBuilder->build();
    }

    public function buildMutationConfigFile(MutantInterface $mutant): string
    {
        return $this->mutationConfigBuilder->build($mutant);
    }

    public function getVersion(): string
    {
        $process = new Process(array_merge(
            $this->findPhp(),
            [
                $this->testFrameworkFinder->find(),
                '--version',
            ]
        ));

        $process->mustRun();

        return $this->versionParser->parse($process->getOutput());
    }
}
