<?php

declare(strict_types=1);

namespace Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\PhpUnitPathProvider;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mockery;

class PhpUnitPathProviderTest extends AbstractBaseProviderTest
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

        $provider = new PhpUnitPathProvider($locatorMock, $consoleMock, $this->getQuestionHelper());

        $provider->get($this->createStreamableInputInterfaceMock(), $this->createOutputInterface(), [], 'phpunit');
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

        $consoleMock->expects($this->once())->method('getQuestion');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitPathProvider($locatorMock, $consoleMock, $dialog);
        $inputPhpUnitPath = realpath(__DIR__ . '/../../Files/phpunit');

        $path = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("{$inputPhpUnitPath}\n")),
            $this->createOutputInterface(),
            [],
            'phpunit'
        );

        $this->assertSame($inputPhpUnitPath, $path);
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

        $provider = new PhpUnitPathProvider($locatorMock, $consoleMock, $dialog);

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
        $locatorMock = Mockery::mock(TestFrameworkConfigLocator::class);

        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andThrow(new \Exception());
        $locatorMock->shouldReceive('locate')->once()->andReturn(true);

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consoleMock->expects($this->once())->method('getQuestion');
        $dialog = $this->getQuestionHelper();

        $provider = new PhpUnitPathProvider($locatorMock, $consoleMock, $dialog);

        $path = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface(),
            [],
            'phpunit'
        );

        $this->assertSame('.', $path); // fallbacks to default value
    }
}