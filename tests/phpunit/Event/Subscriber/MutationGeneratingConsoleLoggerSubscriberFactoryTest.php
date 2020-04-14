<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\CiMutationGeneratingConsoleLoggerSubscriber;
use Infection\Event\Subscriber\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Event\Subscriber\MutationGeneratingConsoleLoggerSubscriberFactory;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class MutationGeneratingConsoleLoggerSubscriberFactoryTest extends TestCase
{
    public function test_it_creates_a_ci_subscriber_if_skips_the_progress_bar(): void
    {
        $factory = new MutationGeneratingConsoleLoggerSubscriberFactory(true);

        $subscriber = $factory->create(new FakeOutput());

        $this->assertInstanceOf(CiMutationGeneratingConsoleLoggerSubscriber::class, $subscriber);
    }

    public function test_it_creates_a_regular_subscriber_if_does_not_skip_the_progress_bar(): void
    {
        $factory = new MutationGeneratingConsoleLoggerSubscriberFactory(false);

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $subscriber = $factory->create($outputMock);

        $this->assertInstanceOf(MutationGeneratingConsoleLoggerSubscriber::class, $subscriber);
    }
}
