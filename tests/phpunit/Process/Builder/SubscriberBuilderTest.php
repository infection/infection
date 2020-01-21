<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Builder;

use Infection\Configuration\Configuration;
use Infection\Configuration\Entry\Logs;
use Infection\Differ\DiffColorizer;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Logger\LoggerFactory;
use Infection\Mutant\MetricsCalculator;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * NOTE:
 * InputInterfaces should be mocked here so that the 'getOption' method with paramater 'no-progress'
 * should return true. Otherwise you will see different results based on wheter its running in CI or not.
 *
 * @group integration Requires some I/O operations
 */
final class SubscriberBuilderTest extends TestCase
{
    public function test_it_registers_the_subscribers_when_debugging(): void
    {
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->exactly(6))->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(Configuration::class);
        $config->expects($this->once())->method('getLogs')->willReturn(
            new Logs(null, null, null, null, null)
        );
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            true,
            true,
            'progress',
            true,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter(),
            new LoggerFactory($calculator, $fs, 'all', false, false)
        );
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_registers_the_subscribers_when_not_debugging(): void
    {
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->exactly(7))->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(Configuration::class);
        $config->expects($this->once())->method('getLogs')->willReturn(
            new Logs(null, null, null, null, null)
        );
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            true,
            false,
            'progress',
            true,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter(),
            new LoggerFactory($calculator, $fs, 'all', false, false)
        );
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_throws_an_exception_when_output_formatter_is_invalid(): void
    {
        $calculator = new MetricsCalculator();
        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher->expects($this->never())->method('addSubscriber');
        $diff = $this->createMock(DiffColorizer::class);
        $config = $this->createMock(Configuration::class);
        $fs = $this->createMock(Filesystem::class);
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = new SubscriberBuilder(
            true,
            true,
            'foo',
            true,
            $calculator,
            $dispatcher,
            $diff,
            $config,
            $fs,
            sys_get_temp_dir(),
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter(),
            new LoggerFactory($calculator, $fs, 'all', false, false)
        );

        $this->expectException(InvalidArgumentException::class);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }
}
