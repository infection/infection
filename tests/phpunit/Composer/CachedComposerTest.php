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

namespace Infection\Tests\Composer;

use Infection\Composer\CachedComposer;
use Infection\Composer\Composer;
use Infection\Composer\Throwable\IncompatibleComposerVersion;
use Infection\Composer\Throwable\UndetectableComposerVersion;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedComposer::class)]
final class CachedComposerTest extends TestCase
{
    use ExpectsThrowables;

    private Composer&MockObject $composerMock;

    private CachedComposer $composer;

    protected function setUp(): void
    {
        $this->composerMock = $this->createMock(Composer::class);

        $this->composer = new CachedComposer($this->composerMock);
    }

    public function test_it_caches_the_composer_version(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn('2.8.0');

        $this->assertSame(
            '2.8.0',
            $this->composer->getVersion(),
        );
        $this->assertSame(
            '2.8.0',
            $this->composer->getVersion(),
        );
    }

    public function test_it_does_not_cache_a_composer_version_failure(): void
    {
        $exception = new UndetectableComposerVersion('Could not detect the Composer version.');
        $hasFailed = false;

        $this->composerMock
            ->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnCallback(
                static function () use (&$hasFailed, $exception): string {
                    if (!$hasFailed) {
                        $hasFailed = true;

                        throw $exception;
                    }

                    return '2.8.0';
                },
            );

        $firstCallResult = $this->expectToThrow($this->composer->getVersion(...));
        $secondCallResult = $this->composer->getVersion();

        $this->assertSame(
            $exception,
            $firstCallResult,
        );
        $this->assertSame(
            '2.8.0',
            $secondCallResult,
        );
    }

    public function test_it_caches_the_composer_version_check(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('checkVersion');

        $this->composer->checkVersion();
        $this->composer->checkVersion();
    }

    public function test_it_does_not_cache_a_composer_version_check_failure(): void
    {
        $exception = IncompatibleComposerVersion::create(
            '1.0.0',
            '^2.0',
        );
        $hasFailed = false;

        $this->composerMock
            ->expects($this->exactly(2))
            ->method('checkVersion')
            ->willReturnCallback(
                static function () use (&$hasFailed, $exception): void {
                    if (!$hasFailed) {
                        $hasFailed = true;

                        throw $exception;
                    }
                },
            );

        $firstCallResult = $this->expectToThrow($this->composer->checkVersion(...));

        $this->assertSame(
            $exception,
            $firstCallResult,
        );

        // Does not throw.
        $this->composer->checkVersion();
    }

    public function test_it_caches_the_vendor_dir(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('getVendorDir')
            ->willReturn('vendor');

        $this->assertSame(
            'vendor',
            $this->composer->getVendorDir(),
        );
        $this->assertSame(
            'vendor',
            $this->composer->getVendorDir(),
        );
    }

    public function test_it_caches_an_unknown_vendor_dir(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('getVendorDir')
            ->willReturn(null);

        $this->assertNull($this->composer->getVendorDir());
        $this->assertNull($this->composer->getVendorDir());
    }

    public function test_it_caches_the_bin_dir(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('getBinDir')
            ->willReturn('vendor/bin');

        $this->assertSame(
            'vendor/bin',
            $this->composer->getBinDir(),
        );
        $this->assertSame(
            'vendor/bin',
            $this->composer->getBinDir(),
        );
    }

    public function test_it_caches_an_unknown_bin_dir(): void
    {
        $this->composerMock
            ->expects($this->once())
            ->method('getBinDir')
            ->willReturn(null);

        $this->assertNull($this->composer->getBinDir());
        $this->assertNull($this->composer->getBinDir());
    }

    public function test_it_delegates_package_installation_every_time(): void
    {
        $this->composerMock
            ->expects($this->exactly(2))
            ->method('requireDevPackage')
            ->with('infection/extension-installer');

        $this->composer->requireDevPackage('infection/extension-installer');
        $this->composer->requireDevPackage('infection/extension-installer');
    }
}
