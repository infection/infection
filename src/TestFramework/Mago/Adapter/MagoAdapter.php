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

namespace Infection\TestFramework\Mago\Adapter;

use function array_merge;
use Closure;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\ShellCommandLineExecutor;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialRunProcessFactory;
use Infection\TestFramework\Common\MutantProcessContainer;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\MutantProcess;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Mago\Process\MagoMutantProcessFactory;
use RuntimeException;
use Safe\Exceptions\PcreException;
use function sprintf;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use function version_compare;

/**
 * @internal
 */
final class MagoAdapter implements TestFramework
{
    /**
     * @param list<string> $staticAnalysisToolOptions
     */
    public function __construct(
        private readonly string $staticAnalysisConfigPath,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly VersionParser $versionParser,
        private readonly array $staticAnalysisToolOptions,
        private readonly ShellCommandLineExecutor $shellCommandLineExecutor,
        private readonly InitialRunProcessFactory $initialRunProcessFactory,
        private readonly MagoMutantProcessFactory $mutantProcessFactory,
        private ?string $version = null,
    ) {
    }

    public function getName(): string
    {
        return 'Mago';
    }

    /**
     * @return array<string>
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

    public function checkRequirements(): void
    {
        $this->assertMinimumVersionSatisfied();
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        $process = $this->initialRunProcessFactory->create($this->getInitialRunCommandLine(), false);
        $process->run($onProgress);

        if (!$process->isSuccessful()) {
            throw InitialStaticAnalysisRunFailed::fromProcess($process, $this->getName());
        }

        return new InitialTestsResult($process->getOutput());
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        return MutantProcessContainer::from(
            $mutant,
            fn (): MutantProcess => $this->mutantProcessFactory->create($mutant),
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

        return $this->versionParser->parse(
            $this->shellCommandLineExecutor->execute($testFrameworkVersionExecutable),
        );
    }
}
