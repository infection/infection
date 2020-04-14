<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Differ\DiffColorizer;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriber;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriberFactory;
use Infection\Metrics\MetricsCalculator;
use Infection\Tests\Fixtures\Console\FakeOutput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class MutationTestingConsoleLoggerSubscriberFactoryTest extends TestCase
{
    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculatorMock;

    /**
     * @var DiffColorizer|MockObject
     */
    private $diffColorizerMock;

    protected function setUp(): void
    {
        $this->metricsCalculatorMock = $this->createMock(MetricsCalculator::class);
        $this->metricsCalculatorMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->diffColorizerMock = $this->createMock(DiffColorizer::class);
        $this->diffColorizerMock
            ->expects($this->never())
            ->method($this->anything())
        ;
    }

    /**
     * @dataProvider valuesProvider
     */
    public function test_it_creates_a_subscriber(string $formatter, bool $showMutations): void
    {
        $factory = new MutationTestingConsoleLoggerSubscriberFactory(
            $this->metricsCalculatorMock,
            $this->diffColorizerMock,
            $showMutations,
            $formatter
        );

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $subscriber = $factory->create($outputMock);

        $this->assertInstanceOf(MutationTestingConsoleLoggerSubscriber::class, $subscriber);
    }

    public function valuesProvider(): iterable
    {
        $formatters = ['dot', 'progress'];
        $showMutationsValues = [true, false];

        foreach ($formatters as $formatter) {
            foreach ($showMutationsValues as $showMutations) {
                yield [$formatter, $showMutations];
            }
        }
    }
}
