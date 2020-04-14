<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\Subscriber\CiInitialTestsConsoleLoggerSubscriber;
use Infection\Event\Subscriber\InitialTestsConsoleLoggerSubscriber;
use Infection\Event\Subscriber\InitialTestsConsoleLoggerSubscriberFactory;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class InitialTestsConsoleLoggerSubscriberFactoryTest extends TestCase
{
    /**
     * @var TestFrameworkAdapter|MockObject
     */
    private $testFrameworkAdapterMock;

    protected function setUp(): void
    {
        $this->testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $this->testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;
    }

    /**
     * @dataProvider debugProvider
     */
    public function test_it_creates_a_ci_subscriber_if_skips_the_progress_bar(bool $debug): void
    {
        $factory = new InitialTestsConsoleLoggerSubscriberFactory(
            true,
            $this->testFrameworkAdapterMock,
            $debug
        );

        $subscriber = $factory->create(new FakeOutput());

        $this->assertInstanceOf(CiInitialTestsConsoleLoggerSubscriber::class, $subscriber);
    }

    /**
     * @dataProvider debugProvider
     */
    public function test_it_creates_a_regular_subscriber_if_does_not_skip_the_progress_bar(bool $debug): void
    {
        $factory = new InitialTestsConsoleLoggerSubscriberFactory(
            false,
            $this->testFrameworkAdapterMock,
            $debug
        );

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $subscriber = $factory->create($outputMock);

        $this->assertInstanceOf(InitialTestsConsoleLoggerSubscriber::class, $subscriber);
    }

    public function debugProvider(): iterable
    {
        yield 'debug enabled' => [true];

        yield 'debug disabled' => [false];
    }
}
