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

use Infection\CannotBeInstantiated;
use Infection\Configuration\Configuration;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFrameworkFactory;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\Mago\Logger\PsrMagoLogger;
use Infection\TestFramework\Mago\Mutant\MagoMutantExecutionResultFactory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @internal
 */
final class MagoAdapterFactory implements TestFrameworkFactory
{
    use CannotBeInstantiated;

    #[Override]
    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
        bool $executeOnlyCoveringTestCases,
        array $filteredSourceFilesToMutate,
        ?string $mapSourceClassToTestStrategy,
        ShellCommandRunner $shellCommandRunner,
        string $sourceDirectoryBasePath,
        bool $useWindowsFilterLimit,
        Filesystem $fileSystem,
        TestFrameworkLogger $testFrameworkLogger,
        LoggerInterface $logger,
        CoverageCheckerFactory $coverageCheckerFactory,
        Configuration $configuration,
        MutantProcessContainerFactory $processFactory,
        TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
    ): MagoAdapter {
        return new MagoAdapter(
            new MagoMutantExecutionResultFactory(),
            $testFrameworkConfigPath,
            $testFrameworkExecutable,
            new CommandLineBuilder(
                new PhpExecutableFinder(),
            ),
            new VersionParser('Mago'),
            $configuration->processTimeout,
            $configuration->getStaticAnalysisToolOptions(),
            $shellCommandRunner,
            new InitialTestsRunner($shellCommandRunner, $testFrameworkLogger),
            new PsrMagoLogger($logger),
        );
    }

    #[Override]
    public static function getAdapterName(): string
    {
        return 'mago';
    }

    #[Override]
    public static function getExecutableName(): string
    {
        return 'mago';
    }
}
