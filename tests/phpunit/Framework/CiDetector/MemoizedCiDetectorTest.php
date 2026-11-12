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

namespace Infection\Tests\Framework\CiDetector;

use Infection\Framework\CiDetector\MemoizedCiDetector;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoizedCiDetector::class)]
final class MemoizedCiDetectorTest extends TestCase
{
    use ExpectsThrowables;

    public function test_it_defers_detection_until_needed(): void
    {
        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->never())
            ->method('detect')
        ;

        new MemoizedCiDetector($decoratedDetector);
    }

    #[DataProvider('detectionOrderProvider')]
    public function test_it_memoizes_the_detected_ci(bool $detectFirst): void
    {
        $ci = $this->createStub(CiInterface::class);
        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->once())
            ->method('detect')
            ->willReturn($ci)
        ;

        $detector = new MemoizedCiDetector($decoratedDetector);

        if ($detectFirst) {
            $this->assertSame($ci, $detector->detect());
        }

        $this->assertTrue($detector->isCiDetected());
        $this->assertSame($ci, $detector->detect());
        $this->assertTrue($detector->isCiDetected());
        $this->assertSame($ci, $detector->detect());
    }

    #[DataProvider('detectionOrderProvider')]
    public function test_it_memoizes_the_absence_of_ci(bool $detectFirst): void
    {
        $exception = new CiNotDetectedException('No CI server detected');

        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->once())
            ->method('detect')
            ->willThrowException($exception)
        ;

        $detector = new MemoizedCiDetector($decoratedDetector);

        if (!$detectFirst) {
            $this->assertFalse($detector->isCiDetected());
        }

        $actual1 = $this->expectToThrow($detector->detect(...));

        $this->assertFalse($detector->isCiDetected());
        $this->assertFalse($detector->isCiDetected());

        $actual2 = $this->expectToThrow($detector->detect(...));

        $this->assertSame($exception, $actual1);
        $this->assertSame($exception, $actual2);
    }

    public static function detectionOrderProvider(): iterable
    {
        yield 'detect() called first' => [true];

        yield 'isCiDetected() called first' => [false];
    }
}
