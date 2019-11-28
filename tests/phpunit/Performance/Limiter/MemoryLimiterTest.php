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

namespace Infection\Tests\Performance\Limiter;

use Composer\XdebugHandler\XdebugHandler;
use Infection\Performance\Limiter\MemoryLimiter;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use LogicException;
use Memory_Aware\FakeAwareAdapter;
use const PHP_SAPI;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class MemoryLimiterTest extends TestCase
{
    private const TEST_DIR_LOCATION = __DIR__ . '/../../Fixtures/tmp-memory-files';

    public static function setUpBeforeClass(): void
    {
        $fs = new Filesystem();
        $fs->mkdir(self::TEST_DIR_LOCATION);
    }

    public static function tearDownAfterClass(): void
    {
        $fs = new Filesystem();
        $fs->remove(self::TEST_DIR_LOCATION);

        $xdebug = new ReflectionClass(XdebugHandler::class);
        $skipped = $xdebug->getProperty('skipped');
        $skipped->setAccessible(true);
        $skipped->setValue(null);
    }

    protected function setUp(): void
    {
        if (ini_get('memory_limit') !== '-1') {
            $this->markTestSkipped('Unable to test if a memory limit is already set.');
        }

        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Unable to run tests if PHPDBG is used.');
        }

        if (XdebugHandler::getSkippedVersion() === '') {
            $xdebug = new ReflectionClass(XdebugHandler::class);
            $skipped = $xdebug->getProperty('skipped');
            $skipped->setAccessible(true);
            $skipped->setValue('infection-fake');

            return;
        }

        if (XdebugHandler::getSkippedVersion() !== 'infection-fake') {
            throw new LogicException('Xdebug handler is active during tests, which it should not be.');
        }
    }

    public function test_it_does_nothing_when_adapter_is_not_memory_limit_aware(): void
    {
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->never())->method($this->anything());

        $process = $this->createMock(Process::class);
        $process->expects($this->never())->method($this->anything());

        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $adapter->expects($this->never())->method($this->anything());

        $memoryLimiter = new MemoryLimiter($fs, 'foo/bar');
        $memoryLimiter->applyMemoryLimitFromProcess($process, $adapter);
    }

    public function test_it_does_not_apply_a_limit_if_no_ini_file_loaded(): void
    {
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->never())->method($this->anything());

        $process = $this->createMock(Process::class);
        $process->expects($this->never())->method($this->anything());

        $memoryLimiter = new MemoryLimiter($fs, false);
        $memoryLimiter->applyMemoryLimitFromProcess($process, new FakeAwareAdapter(10));
    }

    public function test_it_applies_memory_limit_if_possible(): void
    {
        $filename = self::TEST_DIR_LOCATION . '/fake-ini' . microtime() . '.ini';

        if (!touch($filename)) {
            $this->markTestSkipped('Unable to create temporary file for testing purposes, skipping.');
        }

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('appendToFile')->with(
            $filename,
            PHP_EOL . sprintf('memory_limit = %dM', 40.0)
        );

        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('getOutput')->willReturn('foo');

        $adapter = new FakeAwareAdapter(20.0);

        $memoryLimiter = new MemoryLimiter($fs, $filename);
        $memoryLimiter->applyMemoryLimitFromProcess($process, $adapter);
        unlink($filename);
    }

    public function test_it_does_nothing_when_memory_used_is_zero(): void
    {
        $filename = self::TEST_DIR_LOCATION . '/fake-ini' . microtime() . '.ini';

        if (!touch($filename)) {
            $this->markTestSkipped('Unable to create temporary file for testing purposes, skipping.');
        }

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->never())->method($this->anything());

        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('getOutput')->willReturn('foo');

        $adapter = new FakeAwareAdapter(-1);

        $memoryLimiter = new MemoryLimiter($fs, $filename);
        $memoryLimiter->applyMemoryLimitFromProcess($process, $adapter);
        unlink($filename);
    }
}
