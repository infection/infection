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

namespace Infection\StaticAnalysis\Mago\Adapter;

use function array_merge;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\StaticAnalysis\Mago\Mutant\MagoMutantExecutionResultFactory;
use Infection\StaticAnalysis\Mago\Process\MagoMutantProcessFactory;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\VersionParser;
use RuntimeException;
use Safe\Exceptions\PcreException;
use function sprintf;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use function version_compare;

/**
 * @internal
 */
final class MagoAdapter implements StaticAnalysisToolAdapter
{
    /**
     * @param list<string> $staticAnalysisToolOptions
     */
    public function __construct(
        private readonly MagoMutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly string $staticAnalysisConfigPath,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly VersionParser $versionParser,
        private readonly float $timeout,
        private readonly array $staticAnalysisToolOptions,
        private ?string $version = null,
    ) {
    }

    public function getName(): string
    {
        return 'Mago';
    }

    /**
     * @return string[]
     */
    public function getInitialRunCommandLine(): array
    {
        $options = array_merge([
            "--config=$this->staticAnalysisConfigPath",
            'analyze',
        ], $this->staticAnalysisToolOptions);

        return $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            $options,
        );
    }

    public function createMutantProcessFactory(): LazyMutantProcessFactory
    {
        return new MagoMutantProcessFactory(
            $this->mutantExecutionResultFactory,
            $this->staticAnalysisToolExecutable,
            $this->commandLineBuilder,
            $this->timeout,
            $this->staticAnalysisToolOptions,
        );
    }

    /**
     * @throws PcreException|ProcessTimedOutException|RuntimeException|ProcessSignaledException|ProcessFailedException
     */
    public function getVersion(): string
    {
        return $this->version ??= $this->retrieveVersion();
    }

    /**
     * @throws RuntimeException|PcreException
     */
    public function assertMinimumVersionSatisfied(): void
    {
        $version = $this->getVersion();

        if (version_compare($version, '1.23.0', '>=')) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Infection requires Mago version >=1.23.0, but "%s" is installed.',
            $version,
        ));
    }

    /**
     * @throws PcreException|ProcessTimedOutException|RuntimeException|ProcessSignaledException|ProcessFailedException
     */
    private function retrieveVersion(): string
    {
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            ['--version'],
        );

        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();

        return $this->versionParser->parse($process->getOutput());
    }
}
