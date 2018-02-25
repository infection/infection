<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\PhpUnitCustomExecutablePathProvider;
use Infection\Finder\Exception\FinderException;
use Infection\Finder\TestFrameworkFinder;
use Mockery;
use function Infection\Tests\normalizePath as p;

class PhpUnitCustomExecutablePathProviderTest extends AbstractBaseProviderTest
{
    public function test_it_returns_null_if_executable_is_found()
    {
        $finderMock = Mockery::mock(TestFrameworkFinder::class);

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
        $finderMock = Mockery::mock(TestFrameworkFinder::class);

        $finderMock->shouldReceive('find')->once()->andThrow(new FinderException());

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion')->willReturn('foobar');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitCustomExecutablePathProvider($finderMock, $consoleMock, $dialog);
        $customExecutable = p(realpath(__DIR__ . '/../../Fixtures/Files/phpunit/phpunit.phar'));

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

        $finderMock = Mockery::mock(TestFrameworkFinder::class);

        $finderMock->shouldReceive('find')->once()->andThrow(new FinderException());

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion')->willReturn('foobar');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitCustomExecutablePathProvider($finderMock, $consoleMock, $dialog);

        $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface()
        );
    }
}
