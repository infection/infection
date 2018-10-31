<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

        if ($this->isBatchFile($frameworkPath)) {
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
        $frameworkPath = $this->testFrameworkFinder->find();
        $phpIfNeeded = $this->isBatchFile($frameworkPath) ? [] : $this->findPhp();

        $process = new Process(array_merge(
            $phpIfNeeded,
            [
                $frameworkPath,
                '--version',
            ]
        ));

        $process->mustRun();

        return $this->versionParser->parse($process->getOutput());
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

    private function isBatchFile(string $path): bool
    {
        return '.bat' === substr($path, -4);
    }
}
