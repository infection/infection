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

namespace Infection\Tests\Process\Runner;

use DuoClock\TimeSpy;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\ProcessQueue;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessQueue::class)]
final class ProcessQueueTest extends TestCase
{
    private const SIMULATED_TIME_MICROSECONDS = 1_000_000;

    public function test_new_queue_is_empty(): void
    {
        $queue = new ProcessQueue();

        $this->assertTrue($queue->isEmpty());
    }

    public function test_queue_is_not_empty_after_enqueue(): void
    {
        $queue = new ProcessQueue();
        $container = $this->createMock(MutantProcessContainer::class);

        $queue->enqueue($container);

        $this->assertFalse($queue->isEmpty());
    }

    public function test_dequeue_returns_enqueued_items_in_fifo_order(): void
    {
        $queue = new ProcessQueue();
        $container1 = $this->createMock(MutantProcessContainer::class);
        $container2 = $this->createMock(MutantProcessContainer::class);

        $queue->enqueue($container1);
        $queue->enqueue($container2);

        $this->assertSame($container1, $queue->dequeue());
        $this->assertSame($container2, $queue->dequeue());
        $this->assertTrue($queue->isEmpty());
    }

    public function test_enqueue_from_with_exhausted_iterator_returns_zero(): void
    {
        $queue = new ProcessQueue();

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(false);

        $iterator->expects($this->never())
            ->method('current');

        $result = $queue->enqueueFrom($iterator);

        $this->assertSame(0, $result);
        $this->assertTrue($queue->isEmpty());
    }

    public function test_enqueue_from_with_valid_iterator_enqueues_item(): void
    {
        $queue = new ProcessQueue();

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next');

        $result = $queue->enqueueFrom($iterator);

        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertFalse($queue->isEmpty());
        $this->assertSame($container, $queue->dequeue());
    }

    public function test_enqueue_from_respects_max_queue_depth_when_equal(): void
    {
        $queue = new ProcessQueue();

        // Fill queue to capacity
        $queue->enqueue($this->createMock(MutantProcessContainer::class));
        $queue->enqueue($this->createMock(MutantProcessContainer::class));

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->never())
            ->method('valid');
        $iterator->expects($this->never())
            ->method('current');

        $result = $queue->enqueueFrom($iterator, maxQueueDepth: 2);

        $this->assertSame(0, $result);
    }

    public function test_enqueue_from_respects_max_queue_depth_when_exceeded(): void
    {
        $queue = new ProcessQueue();

        // Fill queue beyond capacity
        $queue->enqueue($this->createMock(MutantProcessContainer::class));
        $queue->enqueue($this->createMock(MutantProcessContainer::class));
        $queue->enqueue($this->createMock(MutantProcessContainer::class));

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->never())
            ->method('valid');
        $iterator->expects($this->never())
            ->method('current');

        $result = $queue->enqueueFrom($iterator, maxQueueDepth: 2);

        $this->assertSame(0, $result);
    }

    public function test_enqueue_from_accepts_items_when_below_capacity(): void
    {
        $queue = new ProcessQueue();

        // Add one item
        $queue->enqueue($this->createMock(MutantProcessContainer::class));

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next');

        $result = $queue->enqueueFrom($iterator, maxQueueDepth: 2);

        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function test_enqueue_from_measures_time_correctly(): void
    {
        $clockMock = $this->createMock(TimeSpy::class);
        $queue = new ProcessQueue($clockMock);

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next');

        // Mock two sequential calls to microtime()
        $clockMock->expects($this->exactly(2))
            ->method('microtime')
            ->willReturnOnConsecutiveCalls(1000.0, 1001.0); // 1 second difference

        $result = $queue->enqueueFrom($iterator);

        // Time calculation: (1001.0 - 1000.0) * 1_000_000 = 1_000_000 microseconds
        $this->assertSame(self::SIMULATED_TIME_MICROSECONDS, $result);
    }

    public function test_enqueue_from_time_calculation_uses_subtraction(): void
    {
        // This test kills the Minus mutation
        // Original: (end - start) * NANO_SECONDS_IN_MILLI_SECOND
        // Mutated: (end + start) * NANO_SECONDS_IN_MILLI_SECOND

        $clockMock = $this->createMock(TimeSpy::class);
        $queue = new ProcessQueue($clockMock);

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next');

        $clockMock->expects($this->exactly(2))
            ->method('microtime')
            ->willReturnOnConsecutiveCalls(1000.0, 1001.0);

        $result = $queue->enqueueFrom($iterator);

        // With original: (1001.0 - 1000.0) * 1_000_000 = 1_000_000
        // With mutation: (1001.0 + 1000.0) * 1_000_000 = 2_001_000_000
        $this->assertSame(1_000_000, $result);
    }

    public function test_enqueue_from_advances_iterator(): void
    {
        $queue = new ProcessQueue();

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next'); // This is the critical assertion

        $queue->enqueueFrom($iterator);
    }

    public function test_enqueue_from_with_default_max_depth_of_one(): void
    {
        $queue = new ProcessQueue();

        $container = $this->createMock(MutantProcessContainer::class);
        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(true);
        $iterator->expects($this->once())
            ->method('current')
            ->willReturn($container);
        $iterator->expects($this->once())
            ->method('next');

        // Call without specifying maxQueueDepth - should use default of 1
        $queue->enqueueFrom($iterator);

        $this->assertFalse($queue->isEmpty());

        // Second call should respect capacity
        $iterator2 = $this->createMock(Iterator::class);
        $iterator2->expects($this->never())
            ->method('valid');

        $result = $queue->enqueueFrom($iterator2); // Default maxQueueDepth = 1, queue has 1 item

        $this->assertSame(0, $result);
    }
}
