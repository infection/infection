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

class SourceDirsProviderTest extends TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
    }

    // TODO tear down mockery
    public function test_()
    {

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