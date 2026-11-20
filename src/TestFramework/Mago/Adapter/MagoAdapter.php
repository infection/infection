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
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\Throwable\UnsupportedVersion;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Mago\Logger\MagoLogger;
use Infection\TestFramework\Mago\Process\MagoMutantProcessFactory;
use Override;
use function sprintf;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use function version_compare;

/**
 * @internal
 */
final class MagoAdapter implements StaticAnalysisTestFramework
{
    /**
     * @param list<string> $staticAnalysisToolOptions
     */
    public function __construct(
        private readonly MutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly string $staticAnalysisConfigPath,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly VersionParser $versionParser,
        private readonly float $timeout,
        private readonly array $staticAnalysisToolOptions,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly InitialStaticAnalysisRunner $initialStaticAnalysisRunner,
        private readonly MagoLogger $logger,
        private ?string $version = null,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return 'Mago';
    }

    #[Override]
    public function executeInitialRun(): InitialRunResults
    {
        return $this->initialStaticAnalysisRunner->run($this, $this->getInitialRunCommandLine());
    }

    #[Override]
    public function test(Mutant $mutant): MutantProcessContainer
    {
        $factory = new MagoMutantProcessFactory(
            $this->mutantExecutionResultFactory,
            $this->staticAnalysisToolExecutable,
            $this->commandLineBuilder,
            $this->timeout,
            $this->staticAnalysisToolOptions,
        );

        return new MutantProcessContainer($factory->create($mutant), []);
    }

    #[Override]
    public function hasJUnitReport(): bool
    {
        return false;
    }

    #[Override]
    public function getVersion(): string
    {
        if ($this->version !== null) {
            return $this->version;
        }

        try {
            $this->version = $this->retrieveVersion();
        } catch (SymfonyProcessRuntimeException $exception) {
            $this->logger->logVersionDetectionFailure($exception);

            $this->version = TestFramework::UNKNOWN_VERSION;
        }

        return $this->version;
    }

    #[Override]
    public function checkRequirements(): void
    {
        try {
            $version = $this->getVersion();
        } catch (InvalidVersion $exception) {
            throw new UnsupportedVersion(
                $exception->getMessage(),
                previous: $exception,
            );
        }

        if ($version === TestFramework::UNKNOWN_VERSION) {
            return;
        }

        if (version_compare($version, '1.23.0', '>=')) {
            return;
        }

        throw new UnsupportedVersion(sprintf(
            'Infection requires Mago version >=1.23.0, but "%s" is installed.',
            $version,
        ));
    }

    /**
     * @return string[]
     */
    private function getInitialRunCommandLine(): array
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

    /**
     * @throws InvalidVersion
     * @throws SymfonyProcessRuntimeException
     */
    private function retrieveVersion(): string
    {
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            ['--version'],
        );

        return $this->versionParser->parse(
            $this->shellCommandRunner->mustRun($testFrameworkVersionExecutable),
        );
    }
}
