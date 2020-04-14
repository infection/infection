<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Configuration\Entry\Logs;
use Infection\Event\Subscriber\MutationTestingResultsLoggerSubscriber;
use Infection\Event\Subscriber\MutationTestingResultsLoggerSubscriberFactory;
use Infection\Logger\LoggerFactory;
use Infection\Tests\Fixtures\Console\FakeOutput;
use Infection\Tests\Logger\FakeMutationTestingResultsLogger;
use PHPUnit\Framework\TestCase;

final class MutationTestingResultsLoggerSubscriberFactoryTest extends TestCase
{
    public function test_it_can_create_a_subscriber(): void
    {
        $logsConfig = Logs::createEmpty();

        $output = new FakeOutput();

        $loggerFactoryMock = $this->createMock(LoggerFactory::class);
        $loggerFactoryMock
            ->method('createFromLogEntries')
            ->with($logsConfig, $output)
            ->willReturn(new FakeMutationTestingResultsLogger())
        ;

        $factory = new MutationTestingResultsLoggerSubscriberFactory(
            $loggerFactoryMock,
            $logsConfig
        );

        $subscriber = $factory->create($output);

        $this->assertInstanceOf(MutationTestingResultsLoggerSubscriber::class, $subscriber);
    }
}
