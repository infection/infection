<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework;

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
abstract class AbstractTestFrameworkAdapter implements TestFrameworkAdapter
{
    /**
     * @var string
     */
    private $testFrameworkExecutable;

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
     * @var string|null
     */
    private $cachedVersion;

    /**
     * @var array|null
     */
    private $cachedPhpCmdLine;

    public function __construct(
        string $testFrameworkExecutable,
        InitialConfigBuilder $initialConfigBuilder,
        MutationConfigBuilder $mutationConfigBuilder,
        CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        VersionParser $versionParser
    ) {
        $this->testFrameworkExecutable = $testFrameworkExecutable;
        $this->initialConfigBuilder = $initialConfigBuilder;
        $this->mutationConfigBuilder = $mutationConfigBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
        $this->versionParser = $versionParser;
    }

    abstract public function testsPass(string $output): bool;

    abstract public function getName(): string;

    /**
     * Returns array of arguments to pass them into the Initial Run Symfony Process
     *
     * @return string[]
     */
    public function getInitialTestRunCommandLine(
        string $configPath,
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage
    ): array {
        return $this->getCommandLine($configPath, $extraOptions, $phpExtraArgs, $skipCoverage);
    }

    /**
     * Returns array of arguments to pass them into the Mutant Symfony Process
     *
     * @return string[]
     */
    public function getMutantCommandLine(string $configPath, string $extraOptions): array
    {
        return $this->getCommandLine($configPath, $extraOptions);
    }

    public function buildInitialConfigFile(): string
    {
        return $this->initialConfigBuilder->build($this->getVersion());
    }

    public function buildMutationConfigFile(MutantInterface $mutant): string
    {
        return $this->mutationConfigBuilder->build($mutant);
    }

    public function getVersion(): string
    {
        if ($this->cachedVersion !== null) {
            return $this->cachedVersion;
        }

        $phpIfNeeded = $this->isBatchFile($this->testFrameworkExecutable) ? [] : $this->findPhp();

        $process = new Process(array_merge(
            $phpIfNeeded,
            [
                $this->testFrameworkExecutable,
                '--version',
            ]
        ));

        $process->mustRun();

        $version = 'unknown';

        try {
            $version = $this->versionParser->parse($process->getOutput());
        } catch (\InvalidArgumentException $e) {
            $version = 'unknown';
        } finally {
            $this->cachedVersion = $version;
        }

        return $this->cachedVersion;
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }

    /**
     * @return string[]
     */
    private function getCommandLine(
        string $configPath,
        string $extraOptions,
        array $phpExtraArgs = [],
        bool $skipCoverage = false
    ): array {
        $frameworkArgs = $this->argumentsAndOptionsBuilder->build($configPath, $extraOptions);

        if ($this->isBatchFile($this->testFrameworkExecutable)) {
            return array_merge([$this->testFrameworkExecutable], $frameworkArgs);
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
        if ('cli' === \PHP_SAPI && empty($phpExtraArgs) && is_executable($this->testFrameworkExecutable) && `command -v php`) {
            return array_merge([$this->testFrameworkExecutable], $frameworkArgs);
        }

        /*
         * In all other cases run it with a chosen PHP interpreter
         */
        $commandLineArgs = array_merge(
            $this->findPhp(),
            $phpExtraArgs,
            [$this->testFrameworkExecutable],
            $frameworkArgs
        );

        return array_filter($commandLineArgs);
    }

    /**
     * Need to return string for cases when user run phpdbg with -qrr argument.s
     *
     * @return string[]
     */
    private function findPhp(): array
    {
        if ($this->cachedPhpCmdLine === null) {
            $phpExec = (new PhpExecutableFinder())->find(false);

            if ($phpExec === false) {
                throw FinderException::phpExecutableNotFound();
            }

            $phpCmd[] = $phpExec;

            if (\PHP_SAPI === 'phpdbg') {
                $phpCmd[] = '-qrr';
            }

            $this->cachedPhpCmdLine = $phpCmd;
        }

        return $this->cachedPhpCmdLine;
    }

    private function isBatchFile(string $path): bool
    {
        return '.bat' === substr($path, -4);
    }
}
