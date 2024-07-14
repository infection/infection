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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use function sprintf;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
abstract class AbstractTestFrameworkAdapter implements TestFrameworkAdapter
{
    public function __construct(private readonly string $testFrameworkExecutable, private readonly InitialConfigBuilder $initialConfigBuilder, private readonly MutationConfigBuilder $mutationConfigBuilder, private readonly CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder, private readonly VersionParser $versionParser, private readonly CommandLineBuilder $commandLineBuilder, private ?string $version = null)
    {
    }

    abstract public function testsPass(string $output): bool;

    abstract public function getName(): string;

    abstract public function hasJUnitReport(): bool;

    /**
     * Returns array of arguments to pass them into the Initial Run Process
     *
     * @param string[] $phpExtraArgs
     *
     * @return string[]
     */
    public function getInitialTestRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        return $this->getCommandLine(
            $phpExtraArgs,
            $this->argumentsAndOptionsBuilder->buildForInitialTestsRun($this->buildInitialConfigFile(), $extraOptions),
        );
    }

    /**
     * Returns array of arguments to pass them into the Mutant Process
     *
     * @param TestLocation[] $coverageTests
     *
     * @return string[]
     */
    public function getMutantCommandLine(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions,
    ): array {
        return $this->getCommandLine(
            [],
            $this->argumentsAndOptionsBuilder->buildForMutant(
                $this->buildMutationConfigFile(
                    $coverageTests,
                    $mutatedFilePath,
                    $mutationHash,
                    $mutationOriginalFilePath,
                ),
                $extraOptions,
                $coverageTests,
                $this->getVersion(),
            ),
        );
    }

    public function getVersion(): string
    {
        return $this->version ??= $this->retrieveVersion();
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }

    protected function buildInitialConfigFile(): string
    {
        return $this->initialConfigBuilder->build($this->getVersion());
    }

    /**
     * @param TestLocation[] $tests
     */
    protected function buildMutationConfigFile(
        array $tests,
        string $mutantFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
    ): string {
        return $this->mutationConfigBuilder->build(
            $tests,
            $mutantFilePath,
            $mutationHash,
            $mutationOriginalFilePath,
            $this->getVersion(),
        );
    }

    /**
     * @param string[] $phpExtraArgs
     * @param string[] $testFrameworkArgs
     *
     * @return string[]
     */
    private function getCommandLine(
        array $phpExtraArgs,
        array $testFrameworkArgs,
    ): array {
        return $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            $phpExtraArgs,
            $testFrameworkArgs,
        );
    }

    private function retrieveVersion(): string
    {
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            [],
            ['--version'],
        );

        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();

        return $this->versionParser->parse($process->getOutput());
    }
}
