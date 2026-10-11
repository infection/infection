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

namespace Infection\Tests\Process;

use Infection\Process\ForkWorkerPool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForkWorkerPool::class)]
#[Group('integration')]
#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('posix')]
final class ForkWorkerPoolTest extends TestCase
{
    private ForkWorkerPool $pool;

    protected function setUp(): void
    {
        $this->pool = new ForkWorkerPool();
    }

    protected function tearDown(): void
    {
        $this->pool->killAll();
    }

    public function test_it_starts_the_worker_of_a_thread_once(): void
    {
        $this->assertSame($this->pool->workerFor(1), $this->pool->workerFor(1));
    }

    public function test_it_starts_a_worker_per_thread(): void
    {
        $this->assertNotSame($this->pool->workerFor(1), $this->pool->workerFor(2));
        $this->assertNotSame($this->pool->workerFor('1'), $this->pool->workerFor('2'));
    }

    public function test_it_starts_a_new_worker_for_a_forgotten_thread(): void
    {
        $first = $this->pool->workerFor(1);

        $this->pool->forget(1);

        $this->assertNotSame($first, $this->pool->workerFor(1));
    }

    public function test_forgetting_a_thread_without_a_worker_changes_nothing(): void
    {
        $first = $this->pool->workerFor(1);

        $this->pool->forget(2);

        $this->assertSame($first, $this->pool->workerFor(1));
    }

    public function test_it_starts_a_new_worker_after_it_killed_them_all(): void
    {
        $first = $this->pool->workerFor(1);

        $this->pool->killAll();

        $this->assertNotSame($first, $this->pool->workerFor(1));
    }
}
