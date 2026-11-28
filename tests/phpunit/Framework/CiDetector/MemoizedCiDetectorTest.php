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
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\CiDetectorInterface;
use OndraM\CiDetector\Exception\CiNotDetectedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoizedCiDetector::class)]
final class MemoizedCiDetectorTest extends TestCase
{
    public function test_it_defers_detection_until_needed(): void
    {
        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->never())
            ->method('detect')
        ;

        new MemoizedCiDetector($decoratedDetector);
    }

    public static function ciNameProvider(): iterable
    {
        yield 'GitHub Actions' => [CiDetector::CI_GITHUB_ACTIONS, true];

        yield 'another CI' => [CiDetector::CI_GITLAB, false];
    }

    #[DataProvider('ciNameProvider')]
    public function test_it_memoizes_the_detected_ci(string $ciName, bool $expectedGitHubActions): void
    {
        $ci = $this->createStub(CiInterface::class);
        $ci->method('getCiName')->willReturn($ciName);

        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->once())
            ->method('detect')
            ->willReturn($ci)
        ;

        $detector = new MemoizedCiDetector($decoratedDetector);

        $this->assertSame($ci, $detector->findCi());
        $this->assertTrue($detector->isCiDetected());
        $this->assertSame($expectedGitHubActions, $detector->isGitHubActions());
        $this->assertSame($ci, $detector->findCi());
    }

    public function test_it_memoizes_the_absence_of_ci(): void
    {
        $decoratedDetector = $this->createMock(CiDetectorInterface::class);
        $decoratedDetector
            ->expects($this->once())
            ->method('detect')
            ->willThrowException(new CiNotDetectedException())
        ;

        $detector = new MemoizedCiDetector($decoratedDetector);

        $this->assertNull($detector->findCi());
        $this->assertFalse($detector->isCiDetected());
        $this->assertFalse($detector->isGitHubActions());
        $this->assertNull($detector->findCi());
    }
}
