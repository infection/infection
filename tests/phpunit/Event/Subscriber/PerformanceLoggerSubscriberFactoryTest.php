<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\PerformanceLoggerSubscriberFactory;
use Infection\Resource\Listener\PerformanceLoggerSubscriber;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\TestCase;

final class PerformanceLoggerSubscriberFactoryTest extends TestCase
{
    public function test_it_can_create_a_subscriber(): void
    {
        $factory = new PerformanceLoggerSubscriberFactory(
            new Stopwatch(),
            new TimeFormatter(),
            new MemoryFormatter()
        );

        $subscriber = $factory->create(new FakeOutput());

        $this->assertInstanceOf(PerformanceLoggerSubscriber::class, $subscriber);
    }
}
