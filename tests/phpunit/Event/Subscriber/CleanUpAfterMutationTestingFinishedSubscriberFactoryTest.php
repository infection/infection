<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\CleanUpAfterMutationTestingFinishedSubscriber;
use Infection\Event\Subscriber\CleanUpAfterMutationTestingFinishedSubscriberFactory;
use Infection\Event\Subscriber\NullSubscriber;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class CleanUpAfterMutationTestingFinishedSubscriberFactoryTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->fileSystemMock
            ->expects($this->never())
            ->method($this->anything())
        ;
    }

    public function test_it_creates_a_cleanup_subscriber_if_debug_is_disabled(): void
    {
        $factory = new CleanUpAfterMutationTestingFinishedSubscriberFactory(
            false,
            $this->fileSystemMock,
            '/path/to/tmp'
        );

        $subscriber = $factory->create(new FakeOutput());

        $this->assertInstanceOf(CleanUpAfterMutationTestingFinishedSubscriber::class, $subscriber);
    }

    public function test_it_creates_an_null_subscriber_if_debug_is_enabled(): void
    {
        $factory = new CleanUpAfterMutationTestingFinishedSubscriberFactory(
            true,
            $this->fileSystemMock,
            '/path/to/tmp'
        );

        $subscriber = $factory->create(new FakeOutput());

        $this->assertInstanceOf(NullSubscriber::class, $subscriber);
    }
}
