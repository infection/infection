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

namespace Infection\Tests\TestFramework\Factory;

use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Mago;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Console\ConsoleOutput;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\StaticAnalysisToolExecutableFinder;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Source\Collector\FakeSourceCollector;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Factory;
use Infection\TestFramework\Mago\Adapter\MagoAdapter;
use Infection\TestFramework\PHPStan\Adapter\PHPStanAdapter;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Testing\TestFramework\Debug\DebugStaticAnalysisAdapter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkFactory;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function sprintf;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;

#[Group('integration')]
#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        ConfigurableTestFrameworkFactory::reset();
    }

    public function test_it_throws_an_exception_if_it_cant_find_the_testframework(): void
    {
        $factory = $this->createFactory();

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Invalid name of test framework "Fake Test Framework". Available names are: debug, debug-static-analysis, phpunit, phpstan, mago',
            ),
        );

        $factory->create('Fake Test Framework', false);
    }

    public function test_it_uses_installed_test_framework_adapters(): void
    {
        $result = CompletedProcessBuilder::withMinimalTestData()
            ->withStdout('legacy output')
            ->build()
        ;
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->willReturn($result)
        ;
        $testFrameworkLogger = $this->createMock(TestFrameworkLogger::class);
        $testFrameworkLogger
            ->expects($this->once())
            ->method('initialRunStarted')
            ->with('dummy', '1.0.0')
        ;
        $testFrameworkLogger
            ->expects($this->once())
            ->method('initialRunFinished')
            ->with('dummy', $result->command, 'legacy output')
        ;
        $factory = $this->createFactory(
            [
                'infection/dummy-adapter' => [
                    'install_path' => '/path/to/dummy/adapter/factory.php',
                    'extra' => ['class' => DummyTestFrameworkFactory::class],
                    'version' => '1.0.0',
                ],
            ],
            shellCommandRunner: $shellCommandRunner,
            testFrameworkLogger: $testFrameworkLogger,
        );

        $adapter = $factory->create('dummy', false);

        $this->assertInstanceOf(TestFramework::class, $adapter);
        $initialRun = $adapter->executeInitialRun();

        $this->assertSame('legacy output', $initialRun->output);
    }

    public function test_it_uses_installed_test_frameworks(): void
    {
        $expectedTestFramework = $this->createStub(TestFramework::class);

        ConfigurableTestFrameworkFactory::configure(
            $expectedTestFramework,
            'dummy',
            'dummy',
        );

        $factory = $this->createFactory([
            'infection/dummy' => [
                'install_path' => '/path/to/dummy/factory.php',
                'extra' => ['class' => ConfigurableTestFrameworkFactory::class],
                'version' => '1.0.0',
            ],
        ]);

        $testFramework = $factory->create('dummy', false);

        $this->assertSame($expectedTestFramework, $testFramework);
    }

    public function test_it_creates_phpunit_with_its_custom_executable(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withSourceDirectories('/path/to/project/src')
            ->withPhpUnit(new PhpUnit('config/phpunit', 'bin/phpunit'))
            ->build()
        ;
        $configLocator = $this->createMock(TestFrameworkConfigLocatorInterface::class);
        $configLocator->expects($this->once())
            ->method('locate')
            ->with('phpunit')
            ->willReturn('/path/to/phpunit.xml')
        ;
        $testFrameworkFinder = $this->createMock(TestFrameworkFinder::class);
        $testFrameworkFinder->expects($this->once())
            ->method('find')
            ->with('phpunit', 'bin/phpunit')
            ->willReturn('/path/to/phpunit')
        ;

        $factory = $this->createFactory(
            configuration: $configuration,
            configLocator: $configLocator,
            testFrameworkFinder: $testFrameworkFinder,
        );

        $this->assertSame('PHPUnit', $factory->create('phpunit', false)->getName());
    }

    /**
     * @param class-string<TestFramework> $expectedClass
     */
    #[DataProvider('frameworkProvider')]
    public function test_it_creates_built_in_static_analysis_adapters_without_installed_extensions(string $name, string $expectedClass): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withSourceFilter(new PlainFilter(['/path/to/project/src/Example.php']))
            ->build()
        ;
        $factory = $this->createFactory(
            installedExtensions: [],
            configuration: $configuration,
        );

        $framework = $factory->create($name, true);

        $this->assertInstanceOf($expectedClass, $framework);
    }

    public static function frameworkProvider(): iterable
    {
        yield 'PHPStan' => [StaticAnalysisToolTypes::PHPSTAN, PHPStanAdapter::class];

        yield 'Mago' => [StaticAnalysisToolTypes::MAGO, MagoAdapter::class];

        yield 'debug' => [Factory::DEBUG_STATIC_ANALYSIS, DebugStaticAnalysisAdapter::class];
    }

    #[DataProvider('builtInAdapterProvider')]
    public function test_it_connects_built_in_adapter_version_warnings_to_the_logger(
        string $adapterName,
        string $expectedName,
        string $expectedCustomPath,
    ): void {
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
            ->with($adapterName, $expectedCustomPath)
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
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withPhpStan(new PhpStan(null, '/custom/phpstan'))
            ->withMago(new Mago(null, '/custom/mago'))
            ->build()
        ;
        $factory = $this->createFactory(
            configuration: $configuration,
            shellCommandRunner: $shellCommandRunner,
            staticAnalysisExecutableFinder: $executableFinder,
            staticAnalysisConfigLocator: $configLocator,
            logger: $logger,
        );

        $framework = $factory->create($adapterName, true);

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
        yield 'PHPStan' => [StaticAnalysisToolTypes::PHPSTAN, 'PHPStan', '/custom/phpstan'];

        yield 'Mago' => [StaticAnalysisToolTypes::MAGO, 'Mago', '/custom/mago'];
    }

    /**
     * @param array<string, array<string, mixed>> $installedExtensions
     */
    private function createFactory(
        array $installedExtensions = [],
        ?Configuration $configuration = null,
        ?TestFrameworkConfigLocatorInterface $configLocator = null,
        ?TestFrameworkFinder $testFrameworkFinder = null,
        ?ShellCommandRunner $shellCommandRunner = null,
        ?TestFrameworkLogger $testFrameworkLogger = null,
        ?StaticAnalysisToolExecutableFinder $staticAnalysisExecutableFinder = null,
        ?TestFrameworkConfigLocatorInterface $staticAnalysisConfigLocator = null,
        ?LoggerInterface $logger = null,
    ): Factory {
        $configuration ??= ConfigurationBuilder::withMinimalTestData()->build();
        $fileSystem = $this->createStub(FileSystem::class);
        $fileSystem->method('readFile')->willReturn('<phpunit/>');
        $fileSystem->method('isReadableFile')->willReturn(true);

        return new Factory(
            '',
            '',
            $configLocator ?? $this->createStub(TestFrameworkConfigLocatorInterface::class),
            $testFrameworkFinder ?? $this->createStub(TestFrameworkFinder::class),
            '',
            $configuration,
            new FakeSourceCollector(),
            $installedExtensions,
            $shellCommandRunner ?? $this->createStub(ShellCommandRunner::class),
            $fileSystem,
            $this->createStub(ConsoleOutput::class),
            $testFrameworkLogger ?? $this->createStub(TestFrameworkLogger::class),
            $logger ?? $this->createStub(LoggerInterface::class),
            new CoverageCheckerFactory(
                $configuration,
                JUnitReportLocator::create($fileSystem, ''),
                IndexXmlCoverageLocator::create($fileSystem, ''),
            ),
            $this->createStub(MutantProcessContainerFactory::class),
            $this->createStub(TestFrameworkExtraOptionsFilter::class),
            $staticAnalysisExecutableFinder ?? $this->createStub(StaticAnalysisToolExecutableFinder::class),
            $staticAnalysisConfigLocator ?? $this->createStub(TestFrameworkConfigLocatorInterface::class),
        );
    }
}
