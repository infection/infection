<?php

declare(strict_types=1);

namespace Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\PhpUnitPathProvider;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class PhpUnitPathProviderTest extends TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
    }

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
        $locatorMock = \Mockery::mock(TestFrameworkConfigLocator::class);

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
        $locatorMock = \Mockery::mock(TestFrameworkConfigLocator::class);
        $outputMock = \Mockery::mock(OutputInterface::class);
        $inputMock = \Mockery::mock(InputInterface::class);
        $inputMock->shouldReceive('isInteractive')->once()->andReturn(false);

        $locatorMock->shouldReceive('locate')->twice()->andThrow(new \Exception());

        $consoleMock = $this->getMockBuilder(ConsoleHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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

    protected function getQuestionHelper()
    {
        return new QuestionHelper();
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function createOutputInterface()
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }

    protected function createStreamableInputInterfaceMock($stream = null, $interactive = true)
    {
        $mock = $this->getMockBuilder(StreamableInputInterface::class)->getMock();
        $mock->expects($this->any())
            ->method('isInteractive')
            ->will($this->returnValue($interactive));

        if ($stream) {
            $mock->expects($this->any())
                ->method('getStream')
                ->willReturn($stream);
        }

        return $mock;
    }
}