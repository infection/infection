<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Builder;

use Infection\Config\InfectionConfig;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Mutant\MetricsCalculator;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class SubscriberBuilderTest extends TestCase
{
    public function test_it_registers_the_subscribers_when_debugging(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->exactly(5))
            ->method('getOption')
            ->will($this->returnValueMap(
                [
                    ['formatter', 'progress'],
                    ['show-mutations', true],
                    ['log-verbosity', 'all'],
                    ['debug', true],
                ]
            ));
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(6))->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(InfectionConfig::class);
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            $input,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter()
        );
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_registers_the_subscribers_when_not_debugging(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->exactly(5))
            ->method('getOption')
            ->will($this->returnValueMap(
                [
                    ['formatter', 'progress'],
                    ['show-mutations', true],
                    ['log-verbosity', 'all'],
                    ['debug', false],
                ]
            ));
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(7))->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(InfectionConfig::class);
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            $input,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter()
        );
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_throws_an_exception_when_output_formatter_is_invalid(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->exactly(2))
            ->method('getOption')
            ->will($this->returnValueMap(
                [
                    ['formatter', 'foo'],
                    ['show-mutations', true],
                ]
            ));
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(InfectionConfig::class);
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            $input,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter()
        );

        $this->expectException(\InvalidArgumentException::class);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }
}
