<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\TestFrameworkConfigPathProvider;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestFrameworkConfigPathProviderTest extends AbstractBaseProviderTest
{
    public function test_it_calls_locator_in_the_current_dir()
    {
        $locatorMock = $this->getMockBuilder(TestFrameworkConfigLocator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $locatorMock->expects($this->once())->method('locate');

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new TestFrameworkConfigPathProvider($locatorMock, $consoleMock, $this->getQuestionHelper());

        $result = $provider->get($this->createStreamableInputInterfaceMock(), $this->createOutputInterface(), [], 'phpunit');

        $this->assertNull($result);
    }

    public function test_it_asks_question_if_no_config_is_found_in_current_dir()
    {
        $locatorMock = Mockery::mock(TestFrameworkConfigLocator::class);

        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andReturn(true);

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion')->willReturn('foobar');
        $dialog = $this->getQuestionHelper();

        $provider = new TestFrameworkConfigPathProvider($locatorMock, $consoleMock, $dialog);
        $inputPhpUnitPath = realpath(__DIR__ . '/../../Fixtures/Files/phpunit');

        $path = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$inputPhpUnitPath}\n")),
            $this->createOutputInterface(),
            [],
            'phpunit'
        );

        $this->assertSame($inputPhpUnitPath, $path);
        $this->assertDirectoryExists($path);
    }

    public function test_it_automatically_guesses_path()
    {
        $locatorMock = Mockery::mock(TestFrameworkConfigLocator::class);
        $outputMock = Mockery::mock(OutputInterface::class);
        $inputMock = Mockery::mock(InputInterface::class);

        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andReturn(true);

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->never();

        $dialog = $this->getQuestionHelper();

        $provider = new TestFrameworkConfigPathProvider($locatorMock, $consoleMock, $dialog);

        $path = $provider->get(
            $inputMock,
            $outputMock,
            [],
            'phpunit'
        );

        $this->assertSame('.', $path);
    }

    public function test_validates_incorrect_dir()
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $locatorMock = Mockery::mock(TestFrameworkConfigLocator::class);

        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andReturn(true);

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion')->willReturn('foobar');
        $dialog = $this->getQuestionHelper();

        $provider = new TestFrameworkConfigPathProvider($locatorMock, $consoleMock, $dialog);

        $path = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface(),
            [],
            'phpunit'
        );

        $this->assertSame('.', $path); // fallbacks to default value
    }
}
