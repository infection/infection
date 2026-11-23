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

namespace Infection\Tests\TestFramework\Mago\Adapter;

use Infection\FileSystem\FileSystem;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\Process\DryRunProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Mago\Adapter\MagoAdapter;
use Infection\TestFramework\Mago\Adapter\MagoAdapterFactory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(MagoAdapterFactory::class)]
final class MagoAdapterFactoryTest extends TestCase
{
    public function test_it_can_create_an_adapter_with_the_configured_options(): void
    {
        $result = CompletedProcessBuilder::withMinimalTestData()->withStdout('Analysis passed.')->build();
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/path/to/mago.bat', '--version'])
            ->willReturn('Mago 1.23.0')
        ;
        $shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with(['/path/to/mago.bat', '--config=/path/to/config', 'analyze', '--sort'])
            ->willReturn($result)
        ;
        $testFrameworkLogger = $this->createMock(TestFrameworkLogger::class);
        $testFrameworkLogger
            ->expects($this->once())
            ->method('initialRunStarted')
            ->with('Mago', '1.23.0')
        ;
        $testFrameworkLogger
            ->expects($this->once())
            ->method('initialRunFinished')
            ->with('Mago', $result->command, 'Analysis passed.')
        ;
        $adapter = $this->createAdapter($shellCommandRunner, $testFrameworkLogger, dryRun: true);

        $this->assertSame('Mago', $adapter->getName());
        $initialRun = $adapter->executeInitialRun();

        $this->assertSame('Analysis passed.', $initialRun->output);
    }

    #[DataProvider('runModes')]
    public function test_it_creates_mutant_processes_for_the_configured_run_mode(bool $dryRun): void
    {
        $adapter = $this->createAdapter(
            $this->createStub(ShellCommandRunner::class),
            $this->createStub(TestFrameworkLogger::class),
            $dryRun,
        );
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $attempt = $adapter->test($mutant)->getCurrent();
        $this->assertInstanceOf(MutantProcess::class, $attempt);
        $process = $attempt->getProcess();

        $this->assertSame(32.3, $process->getTimeout());
        $this->assertSame($dryRun, $process instanceof DryRunProcess);
    }

    public static function runModes(): iterable
    {
        yield 'normal' => [false];

        yield 'dry run' => [true];
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('mago', MagoAdapterFactory::getAdapterName());
    }

    public function test_it_has_an_executable(): void
    {
        $this->assertSame('mago', MagoAdapterFactory::getExecutableName());
    }

    private function createAdapter(
        ShellCommandRunner $shellCommandRunner,
        TestFrameworkLogger $testFrameworkLogger,
        bool $dryRun,
    ): MagoAdapter {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withDryRun($dryRun)
            ->withTimeout(32.3)
            ->withStaticAnalysisToolOptions('--sort')
            ->build()
        ;
        $fileSystem = $this->createStub(FileSystem::class);

        return MagoAdapterFactory::create(
            testFrameworkExecutable: '/path/to/mago.bat',
            tmpDir: '/tmp',
            testFrameworkConfigPath: '/path/to/config',
            testFrameworkConfigDir: null,
            jUnitFilePath: '',
            projectDir: '/path/to/project',
            sourceDirectories: ['/path/to/project/src'],
            skipCoverage: true,
            executeOnlyCoveringTestCases: false,
            filteredSourceFilesToMutate: [],
            mapSourceClassToTestStrategy: null,
            shellCommandRunner: $shellCommandRunner,
            sourceDirectoryBasePath: '/path/to/project',
            useWindowsFilterLimit: false,
            fileSystem: $fileSystem,
            testFrameworkLogger: $testFrameworkLogger,
            logger: new NullLogger(),
            coverageCheckerFactory: new CoverageCheckerFactory(
                $configuration,
                JUnitReportLocator::create($fileSystem, ''),
                IndexXmlCoverageLocator::create($fileSystem, ''),
            ),
            configuration: $configuration,
            testFrameworkExtraOptionsFilter: $this->createStub(TestFrameworkExtraOptionsFilter::class),
        );
    }
}
