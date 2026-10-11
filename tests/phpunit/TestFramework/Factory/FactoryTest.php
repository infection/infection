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
use Infection\Configuration\Entry\PhpUnit;
use Infection\Console\ConsoleOutput;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Source\Collector\FakeSourceCollector;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Factory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

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
                'Invalid name of test framework "Fake Test Framework". Available names are: debug, phpunit',
            ),
        );

        $factory->create('Fake Test Framework', false);
    }

    public function test_it_uses_installed_test_framework_adapters(): void
    {
        $factory = $this->createFactory([
            'infection/dummy-adapter' => [
                'install_path' => '/path/to/dummy/adapter/factory.php',
                'extra' => ['class' => DummyTestFrameworkFactory::class],
                'version' => '1.0.0',
            ],
        ]);

        $adapter = $factory->create('dummy', false);

        $this->assertInstanceOf(TestFramework::class, $adapter);
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
            ->withSourceDirectories('src')
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
     * @param array<string, array<string, mixed>> $installedExtensions
     */
    private function createFactory(
        array $installedExtensions = [],
        ?Configuration $configuration = null,
        ?TestFrameworkConfigLocatorInterface $configLocator = null,
        ?TestFrameworkFinder $testFrameworkFinder = null,
    ): Factory {
        $configuration ??= ConfigurationBuilder::withMinimalTestData()->build();
        $fileSystem = $this->createStub(FileSystem::class);
        $fileSystem->method('readFile')->willReturn('<phpunit/>');

        return new Factory(
            '',
            '',
            $configLocator ?? $this->createStub(TestFrameworkConfigLocatorInterface::class),
            $testFrameworkFinder ?? $this->createStub(TestFrameworkFinder::class),
            '',
            $configuration,
            new FakeSourceCollector(),
            $installedExtensions,
            $this->createStub(ShellCommandRunner::class),
            $fileSystem,
            $this->createStub(ConsoleOutput::class),
            new CoverageCheckerFactory(
                $configuration,
                JUnitReportLocator::create($fileSystem, ''),
                IndexXmlCoverageLocator::create($fileSystem, ''),
            ),
            $this->createStub(InitialTestsRunner::class),
            $this->createStub(MutantProcessContainerFactory::class),
            $this->createStub(TestFrameworkExtraOptionsFilter::class),
        );
    }
}
