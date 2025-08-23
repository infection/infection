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

namespace Infection\Tests\NewSrc\AST\AridCodeDetector;

use newSrc\AST\AridCodeDetector\AridCodeDetector;
use newSrc\AST\AridCodeDetector\CodeDetectorRegistry;
use PhpParser\Node\Stmt\Echo_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeDetectorRegistry::class)]
final class CodeDetectorRegistryTest extends TestCase
{
    public function test_it_returns_false_when_no_detectors_are_registered(): void
    {
        $registry = new CodeDetectorRegistry();
        $node = new Echo_([]);

        $result = $registry->isArid($node);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_all_detectors_return_false(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $detector2
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertFalse($result);
    }

    public function test_it_returns_true_when_any_detector_returns_true(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(false);

        $detector2
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(true);

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertTrue($result);
    }

    public function test_it_stops_at_the_first_detector_returning_true(): void
    {
        $detector1 = $this->createMock(AridCodeDetector::class);
        $detector2 = $this->createMock(AridCodeDetector::class);

        $node = new Echo_([]);

        $detector1
            ->expects($this->once())
            ->method('isArid')
            ->with($node)
            ->willReturn(true);

        $detector2
            ->expects($this->never())
            ->method('isArid');

        $registry = new CodeDetectorRegistry(
            $detector1,
            $detector2,
        );

        $result = $registry->isArid($node);

        $this->assertTrue($result);
    }
}
