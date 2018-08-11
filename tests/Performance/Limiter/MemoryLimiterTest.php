<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Limiter;

use Infection\Performance\Limiter\MemoryLimiter;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Memory_Aware\FakeAwareAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MemoryLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        if (ini_get('memory_limit') !== '-1') {
            $this->markTestSkipped('Unable to test if a memory limit is already set.');
        }

        if (\PHP_SAPI === 'phpdbg' || \extension_loaded('xdebug')) {
            $this->markTestSkipped('Unable to run tests if PHPDBG or xdebug is active.');
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

    public function test_it_applies_memory_limit_if_possible(): void
    {
        $filename = sys_get_temp_dir() . '/fake-ini' . microtime() . '.ini';

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
    }

    public function test_it_does_nothing_when_memory_used_is_zero(): void
    {
        $filename = sys_get_temp_dir() . '/fake-ini' . microtime() . '.ini';

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
    }
}
