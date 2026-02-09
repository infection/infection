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

namespace Infection\StaticAnalysis\PHPStan\Adapter;

use function array_merge;
use function explode;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\StaticAnalysis\PHPStan\Process\PHPStanMutantProcessFactory;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\VersionParser;
use RuntimeException;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function version_compare;

/**
 * @internal
 */
final class PHPStanAdapter implements StaticAnalysisToolAdapter
{
    private const VERSION_1 = 1;

    private const VERSION_2 = 2;

    /**
     * @param list<string> $staticAnalysisToolOptions
     */
    public function __construct(
        private readonly Filesystem $fileSystem,
        private readonly PHPStanMutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly string $staticAnalysisConfigPath,
        private readonly string $staticAnalysisToolExecutable,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly VersionParser $versionParser,
        private readonly float $timeout,
        private readonly string $tmpDir,
        private readonly array $staticAnalysisToolOptions,
        private ?string $version = null,
    ) {
    }

    public function getName(): string
    {
        return 'PHPStan';
    }

    /**
     * @return string[]
     */
    public function getInitialRunCommandLine(): array
    {
        // we can't rely on stderr because it's used for other output (non-error)
        // see https://github.com/phpstan/phpstan/issues/11352#issuecomment-2233403781

        $options = array_merge([
            "--configuration=$this->staticAnalysisConfigPath",
            // todo [phpstan-integration] add --stop-on-first-error when it's implemented on PHPStan side
        ], $this->staticAnalysisToolOptions);

        return $this->commandLineBuilder->build(
            $this->staticAnalysisToolExecutable,
            [],
            $options,
        );
    }

    public function createMutantProcessFactory(): LazyMutantProcessFactory
    {
        return new PHPStanMutantProcessFactory(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            $this->staticAnalysisConfigPath,
            $this->staticAnalysisToolExecutable,
            $this->commandLineBuilder,
            $this->timeout,
            $this->tmpDir,
            $this->staticAnalysisToolOptions,
        );
    }

    public function getVersion(): string
    {
        return $this->version ??= $this->retrieveVersion();
    }

    public function assertMinimumVersionSatisfied(): void
    {
        $version = $this->getVersion();

        // running on phpstan-src itself
        if (str_starts_with($version, 'dev-')) {
            return;
        }

        $majorVersion = (int) explode('.', $version)[0];

        // we assume all versions greater than 2.1.17 have needed functionality
        if ($majorVersion > self::VERSION_2) {
            return;
        }

        if (
            $majorVersion === self::VERSION_2
            && (
                version_compare($version, '2.1.17', '>=')
                || str_starts_with($version, '2.1.x-dev') // allow dev versions for development
            )
        ) {
            return;
        }

        if (
            $majorVersion === self::VERSION_1
            && (
                version_compare($version, '1.12.27', '>=')
                || str_starts_with($version, '1.12.x-dev') // allow dev versions for development
            )
        ) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Infection requires PHPStan version >=1.12.27 or >=2.1.17, but "%s" is installed.',
            $version,
        ));
    }

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
