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

namespace Infection\Tests\StaticAnalysis;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\StaticAnalysisToolExecutableFinder;
use Infection\Process\DryRunProcess;
use Infection\StaticAnalysis\StaticAnalysisToolFactory;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\NullStaticAnalysisTestFramework;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutant\MutantBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[CoversClass(StaticAnalysisToolFactory::class)]
final class StaticAnalysisToolFactoryTest extends TestCase
{
    public function test_it_throws_an_exception_if_it_cant_find_sa_tool(): void
    {
        $factory = $this->createFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid name of static analysis tool "Fake SA Tool". Available names are: phpstan, mago, debug');

        $factory->create('Fake SA Tool', 30);
    }

    public function test_it_creates_a_null_framework_when_static_analysis_is_disabled(): void
    {
        $this->assertInstanceOf(NullStaticAnalysisTestFramework::class, $this->createFactory()->create(null, 30));
    }

    public function test_it_wraps_the_static_analysis_adapter_in_a_test_framework(): void
    {
        $framework = $this->createFactory()->create(StaticAnalysisToolTypes::DEBUG, 30);

        $this->assertInstanceOf(TestFramework::class, $framework);
        $this->assertSame('Debug', $framework->getName());
    }

    #[DataProvider('mutantProcessProvider')]
    public function test_it_configures_mutant_processes_through_the_bridge(
        string $tool,
        bool $dryRun,
        string $expectedProcessClass,
    ): void {
        $framework = $this->createFactory($dryRun)->create($tool, 30);
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $framework->test($mutant)->getCurrent()->getProcess();

        $processClass = $process::class;
        $this->assertSame($expectedProcessClass, $processClass);
        $this->assertSame(30.0, $process->getTimeout());
    }

    public static function mutantProcessProvider(): iterable
    {
        yield 'ordinary debug process' => [StaticAnalysisToolTypes::DEBUG, false, Process::class];

        yield 'dry-run debug process' => [StaticAnalysisToolTypes::DEBUG, true, DryRunProcess::class];

        yield 'ordinary Mago process' => [StaticAnalysisToolTypes::MAGO, false, Process::class];

        yield 'dry-run Mago process' => [StaticAnalysisToolTypes::MAGO, true, DryRunProcess::class];

        yield 'ordinary PHPStan process' => [StaticAnalysisToolTypes::PHPSTAN, false, Process::class];

        yield 'dry-run PHPStan process' => [StaticAnalysisToolTypes::PHPSTAN, true, DryRunProcess::class];
    }

    private function createFactory(bool $dryRun = false): StaticAnalysisToolFactory
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withDryRun($dryRun)
            ->build()
        ;
        $executableFinder = $this->createStub(StaticAnalysisToolExecutableFinder::class);
        $executableFinder->method('find')->willReturn('/path/to/analyser');

        $phpExecutableFinder = $this->createStub(PhpExecutableFinder::class);
        $phpExecutableFinder->method('find')->willReturn('/path/to/php');

        $configLocator = $this->createStub(TestFrameworkConfigLocatorInterface::class);
        $configLocator->method('locate')->willReturn('/path/to/config');

        return new StaticAnalysisToolFactory(
            $configuration,
            $executableFinder,
            $configLocator,
            $this->createStub(ShellCommandRunner::class),
            $phpExecutableFinder,
            $this->createStub(FileSystem::class),
            $this->createStub(EventDispatcher::class),
        );
    }
}
