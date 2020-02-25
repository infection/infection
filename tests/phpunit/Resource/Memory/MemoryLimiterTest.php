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

namespace Infection\Tests\Resource\Memory;

use Composer\XdebugHandler\XdebugHandler;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\Tests\FileSystem\FileSystemTestCase;
use InvalidArgumentException;
use LogicException;
use Memory_Aware\FakeAwareAdapter;
use function microtime;
use const PHP_EOL;
use const PHP_SAPI;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use function Safe\ini_get;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @group integration Requires some I/O operations
 */
final class MemoryLimiterTest extends FileSystemTestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var Process|MockObject
     */
    private $processMock;

    /**
     * @var AbstractTestFrameworkAdapter|MockObject
     */
    private $adapterMock;

    public static function setUpBeforeClass(): void
    {
        if (XdebugHandler::getSkippedVersion() === '') {
            $skipped = (new ReflectionClass(XdebugHandler::class))->getProperty('skipped');
            $skipped->setAccessible(true);
            $skipped->setValue('infection-fake');
        }

        if (XdebugHandler::getSkippedVersion() !== 'infection-fake') {
            throw new LogicException('Did not expect the Xdebug handler to be active during the tests');
        }
    }

    public static function tearDownAfterClass(): void
    {
        $skipped = (new ReflectionClass(XdebugHandler::class))->getProperty('skipped');
        $skipped->setAccessible(true);
        $skipped->setValue(null);

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        if (ini_get('memory_limit') !== '-1') {
            $this->markTestSkipped('Unable to test if a memory limit is already set');
        }

        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Unable to run tests if PHPDBG is used');
        }

        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->processMock = $this->createMock(Process::class);
        $this->adapterMock = $this->createMock(AbstractTestFrameworkAdapter::class);

        parent::setUp();
    }

    public function test_it_throws_a_friendly_error_when_the_ini_value_is_incorrect(): void
    {
        try {
            new MemoryLimiter($this->fileSystemMock, true);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the iniLocation to either be a string or false. Got "true"',
                $exception->getMessage()
            );
        }
    }

    public function test_it_does_nothing_when_adapter_is_not_memory_limit_aware(): void
    {
        $memoryLimiter = new MemoryLimiter($this->fileSystemMock, 'foo/bar');

        $memoryLimiter->applyMemoryLimitFromProcess($this->processMock, $this->adapterMock);
    }

    public function test_it_does_not_apply_a_limit_if_no_ini_file_loaded(): void
    {
        $memoryLimiter = new MemoryLimiter($this->fileSystemMock, 'foo/bar');

        $memoryLimiter->applyMemoryLimitFromProcess(
            $this->processMock,
            new FakeAwareAdapter(10)
        );
    }

    public function test_it_applies_memory_limit_if_possible(): void
    {
        $filename = $this->tmp . '/fake-ini' . microtime() . '.ini';

        $this->fileSystemMock
            ->expects($this->once())
            ->method('appendToFile')
            ->with(
                $filename,
                PHP_EOL . sprintf('memory_limit = %dM', 40.0)
        );

        $this->processMock
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('foo')
        ;

        $adapter = new FakeAwareAdapter(20.0);

        $memoryLimiter = new MemoryLimiter($this->fileSystemMock, $filename);

        $memoryLimiter->applyMemoryLimitFromProcess($this->processMock, $adapter);
    }

    public function test_it_does_nothing_when_memory_used_is_zero(): void
    {
        $filename = $this->tmp . '/fake-ini' . microtime() . '.ini';

        $this->processMock
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('foo')
        ;

        $adapter = new FakeAwareAdapter(-1);

        $memoryLimiter = new MemoryLimiter($this->fileSystemMock, $filename);

        $memoryLimiter->applyMemoryLimitFromProcess($this->processMock, $adapter);
    }
}
