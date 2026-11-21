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

use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\StaticAnalysisToolExecutableFinder;
use Infection\StaticAnalysis\StaticAnalysisToolFactory;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\NullStaticAnalysisTestFramework;
use Infection\Tests\Configuration\ConfigurationBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function sprintf;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;

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

    #[DataProvider('builtInAdapterProvider')]
    public function test_it_connects_built_in_adapter_version_warnings_to_the_logger(string $adapterName, string $expectedName): void
    {
        $configLocator = $this->createMock(TestFrameworkConfigLocatorInterface::class);
        $configLocator
            ->expects($this->once())
            ->method('locate')
            ->with($adapterName)
            ->willReturn('/path/to/config')
        ;
        $executableFinder = $this->createMock(StaticAnalysisToolExecutableFinder::class);
        $executableFinder
            ->expects($this->once())
            ->method('find')
            ->with($adapterName, '')
            ->willReturn('/path/to/tool.bat')
        ;
        $exception = new SymfonyProcessRuntimeException('Could not launch the version command.');
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/path/to/tool.bat', '--version'])
            ->willThrowException($exception)
        ;
        $logger = new TestLogger();
        $factory = $this->createFactory($executableFinder, $configLocator, $shellCommandRunner, $logger);

        $framework = $factory->create($adapterName, 30);

        $this->assertSame($expectedName, $framework->getName());
        $this->assertSame(TestFramework::UNKNOWN_VERSION, $framework->getVersion());
        $this->assertSame(TestFramework::UNKNOWN_VERSION, $framework->getVersion());

        $expected = [[
            'level' => LogLevel::WARNING,
            'message' => sprintf('Could not determine the %s version. Continuing with an unknown version.', $expectedName),
            'context' => ['exception' => $exception],
        ]];

        $this->assertSame($expected, $logger->records);
    }

    public static function builtInAdapterProvider(): iterable
    {
        yield 'PHPStan' => [StaticAnalysisToolTypes::PHPSTAN, 'PHPStan'];

        yield 'Mago' => [StaticAnalysisToolTypes::MAGO, 'Mago'];
    }

    private function createFactory(
        ?StaticAnalysisToolExecutableFinder $executableFinder = null,
        ?TestFrameworkConfigLocatorInterface $configLocator = null,
        ?ShellCommandRunner $shellCommandRunner = null,
        ?LoggerInterface $logger = null,
    ): StaticAnalysisToolFactory {
        return new StaticAnalysisToolFactory(
            ConfigurationBuilder::withMinimalTestData()->build(),
            $executableFinder ?? $this->createStub(StaticAnalysisToolExecutableFinder::class),
            $configLocator ?? $this->createStub(TestFrameworkConfigLocatorInterface::class),
            $shellCommandRunner ?? $this->createStub(ShellCommandRunner::class),
            $this->createStub(PhpExecutableFinder::class),
            $this->createStub(FileSystem::class),
            $this->createStub(TestFrameworkLogger::class),
            $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
