<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\PhpUnitCustomExecutablePathProvider;
use Infection\Finder\Exception\TestFrameworkExecutableFinderNotFound;
use Infection\Finder\TestFrameworkExecutableFinder;
use Mockery;

class PhpUnitCustomExecutablePathProviderTest extends AbstractBaseProviderTest
{
    public function test_it_returns_null_if_executable_is_found()
    {
        $finderMock = Mockery::mock(TestFrameworkExecutableFinder::class);

        $finderMock->shouldReceive('find')->once();

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new PhpUnitCustomExecutablePathProvider($finderMock, $consoleMock, $this->getQuestionHelper());

        $result = $provider->get($this->createStreamableInputInterfaceMock(), $this->createOutputInterface());

        $this->assertNull($result);
    }

    public function test_it_asks_question_if_no_config_is_found_in_current_dir()
    {
        $finderMock = Mockery::mock(TestFrameworkExecutableFinder::class);

        $finderMock->shouldReceive('find')->once()->andThrow(new TestFrameworkExecutableFinderNotFound());

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitCustomExecutablePathProvider($finderMock, $consoleMock, $dialog);
        $customExecutable = realpath(__DIR__ . '/../../Files/phpunit/phpunit.phar');

        $path = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$customExecutable}\n")),
            $this->createOutputInterface()
        );

        $this->assertSame($customExecutable, $path);
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     */
    public function test_validates_incorrect_dir()
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $finderMock = Mockery::mock(TestFrameworkExecutableFinder::class);

        $finderMock->shouldReceive('find')->once()->andThrow(new TestFrameworkExecutableFinderNotFound());

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitCustomExecutablePathProvider($finderMock, $consoleMock, $dialog);

        $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface()
        );
    }
}