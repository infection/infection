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

use Infection\Composer\Composer;
use Infection\Composer\ComposerWithBinDirFallback;
use Infection\Composer\Throwable\IncompatibleComposerVersion;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(ComposerWithBinDirFallback::class)]
final class ComposerWithBinDirFallbackTest extends FileSystemTestCase
{
    use ExpectsThrowables;

    private Composer&MockObject $decoratedComposer;

    private ComposerWithBinDirFallback $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decoratedComposer = $this->createMock(Composer::class);

        $this->composer = new ComposerWithBinDirFallback(
            $this->decoratedComposer,
            $this->tmp . '/vendor/bin',
        );
    }

    public function test_it_delegates_version_detection(): void
    {
        $this->decoratedComposer
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn('2.8.10');

        $this->assertSame(
            '2.8.10',
            $this->composer->getVersion(),
        );
    }

    public function test_it_delegates_version_check(): void
    {
        $exception = IncompatibleComposerVersion::create(
            '1.10.27',
            '^2.0',
        );

        $this->decoratedComposer
            ->expects($this->once())
            ->method('checkVersion')
            ->willThrowException($exception);

        $throwable = $this->expectToThrow($this->composer->checkVersion(...));

        $this->assertSame(
            $exception,
            $throwable,
        );
    }

    public function test_it_delegates_vendor_dir_detection(): void
    {
        $this->decoratedComposer
            ->expects($this->once())
            ->method('getVendorDir')
            ->willReturn('vendor');

        $this->assertSame(
            'vendor',
            $this->composer->getVendorDir(),
        );
    }

    public function test_it_uses_detected_bin_dir(): void
    {
        $this->decoratedComposer
            ->expects($this->once())
            ->method('getBinDir')
            ->willReturn('custom-bin');

        $this->assertSame(
            'custom-bin',
            $this->composer->getBinDir(),
        );
    }

    public function test_it_falls_back_to_the_default_bin_dir_when_it_exists(): void
    {
        (new Filesystem())->mkdir($this->tmp . '/vendor/bin');

        $this->decoratedComposer
            ->expects($this->once())
            ->method('getBinDir')
            ->willReturn(null);

        $this->assertSame(
            $this->tmp . '/vendor/bin',
            $this->composer->getBinDir(),
        );
    }

    public function test_it_returns_null_when_the_bin_dir_is_not_detected_and_the_fallback_bin_dir_does_not_exist(): void
    {
        $this->decoratedComposer
            ->expects($this->once())
            ->method('getBinDir')
            ->willReturn(null);

        $this->assertNull($this->composer->getBinDir());
    }

    public function test_it_delegates_package_installation(): void
    {
        $this->decoratedComposer
            ->expects($this->once())
            ->method('requireDevPackage')
            ->with('infection/extension-installer');

        $this->composer->requireDevPackage('infection/extension-installer');
    }
}
