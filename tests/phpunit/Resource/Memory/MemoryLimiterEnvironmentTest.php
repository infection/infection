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
use Infection\Resource\Memory\MemoryLimiterEnvironment;
use const PHP_SAPI;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\ini_get;
use function Safe\ini_set;

/**
 * @group integration
 */
final class MemoryLimiterEnvironmentTest extends TestCase
{
    /**
     * @var string|null
     */
    private $originalMemoryLimit;

    /**
     * @var MemoryLimiterEnvironment
     */
    private $environment;

    protected function setUp(): void
    {
        $this->originalMemoryLimit = ini_get('memory_limit');

        $this->environment = new MemoryLimiterEnvironment();
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->originalMemoryLimit);
    }

    /**
     * @dataProvider memoryLimitProvider
     */
    public function test_it_can_detect_if_a_memory_limit_is_set(string $memoryLimit, bool $expected): void
    {
        ini_set('memory_limit', $memoryLimit);

        $this->assertSame($expected, $this->environment->hasMemoryLimitSet());
    }

    public function test_it_uses_the_system_ini_if_PHPDBG_is_enabled(): void
    {
        if (PHP_SAPI !== 'phpdbg') {
            $this->markTestSkipped('This test requires PHPDBG');
        }

        $this->assertTrue($this->environment->isUsingSystemIni());
    }

    public function test_it_uses_the_system_ini_if_Xdebug_handler_is_not_detected(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('This test requires running without PHPDBG');
        }

        // We don't expect the Xdebug handler to be executed for the tests hence
        // there is no need to disable it here
        $this->assertTrue($this->environment->isUsingSystemIni());
    }

    public function test_it_uses_the_system_ini_if_PHPDBG_is_enabled_and_Xdebug_handler_is_not_detected(): void
    {
        if (PHP_SAPI !== 'phpdbg') {
            $this->markTestSkipped('This test requires PHPDBG');
        }

        // We don't expect the Xdebug handler to be executed for the tests hence
        // there is no need to disable it here
        $this->assertTrue($this->environment->isUsingSystemIni());
    }

    public function test_it_does_not_use_the_system_ini_if_PHPDBG_is_disabled_and_Xdebug_handler_is_detected(): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('This test requires running without PHPDBG');
        }

        $skipped = (new ReflectionClass(XdebugHandler::class))->getProperty('skipped');
        $skipped->setAccessible(true);
        $skipped->setValue('infection-fake');

        try {
            $this->assertFalse($this->environment->isUsingSystemIni());
        } finally {
            // Restore original value
            $skipped->setValue(null);
        }
    }

    public static function memoryLimitProvider(): iterable
    {
        yield 'no limit' => [
            '-1',
            false,
        ];

        yield 'limit' => [
            '512M',
            true,
        ];

        yield 'invalid limit' => [
            '-512M',
            true,
        ];

        yield 'limit without unit' => [
            '268435456',    // 256M
            true,
        ];
    }
}
