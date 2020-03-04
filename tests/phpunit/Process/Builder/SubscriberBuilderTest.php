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
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;

/**
 * NOTE:
 * InputInterfaces should be mocked here so that the 'getOption' method with paramater 'no-progress'
 * should return true. Otherwise you will see different results based on wheter its running in CI or not.
 *
 * @group integration
 */
final class SubscriberBuilderTest extends TestCase
{
    public function test_it_registers_the_subscribers_when_debugging(): void
    {
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = $this->makeSubscriberBuilder(true, 'progress', false, 5);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_registers_the_subscribers_when_not_debugging(): void
    {
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = $this->makeSubscriberBuilder(false, 'progress', false, 6);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_registers_the_subscribers_without_progress(): void
    {
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = $this->makeSubscriberBuilder(false, 'progress', true, 5);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    public function test_it_throws_an_exception_when_output_formatter_is_invalid(): void
    {
        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $output = $this->createMock(OutputInterface::class);

        $subscriberBuilder = $this->makeSubscriberBuilder(true, 'invalid', false, 2, 0);

        $this->expectException(InvalidArgumentException::class);
        $subscriberBuilder->registerSubscribers($adapter, $output);
    }

    private function makeSubscriberBuilder(
        bool $debug,
        string $formatter,
        bool $noProgress,
        int $addSubscriber,
        int $getLogs = 1
    ): SubscriberBuilder {
        $calculator = new MetricsCalculator();

        $dispatcher = $this->createMock(EventDispatcher::class);
        $dispatcher
            ->expects($this->exactly($addSubscriber))
            ->method('addSubscriber')
        ;

        $config = $this->createMock(Configuration::class);
        $config
            ->expects($this->exactly($getLogs))
            ->method('getLogs')->willReturn(
                new Logs(null, null, null, null, null)
            )
        ;

        $fileSystemMock = $this->createMock(Filesystem::class);

        return new SubscriberBuilder(
            true,
            $debug,
            $formatter,
            $noProgress,
            $calculator,
            $dispatcher,
            $this->createMock(DiffColorizer::class),
            $config,
            $fileSystemMock,
            sys_get_temp_dir(),
            new Stopwatch(),
            new TimeFormatter(),
            new MemoryFormatter(),
            new LoggerFactory(
                $calculator,
                $fileSystemMock,
                'all',
                false,
                false
            )
        );
    }
}
