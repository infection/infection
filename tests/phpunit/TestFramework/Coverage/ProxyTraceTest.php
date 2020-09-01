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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\ProxyTrace;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class ProxyTraceTest extends TestCase
{
    public function test_it_exposes_its_source_file_file_info(): void
    {
        $fileInfoMock = $this->createMock(SplFileInfo::class);

        $actual = (new ProxyTrace($fileInfoMock, []))->getSourceFileInfo();

        $this->assertSame(
            $fileInfoMock,
            $actual
        );
    }

    public function test_it_exposes_its_source_file_real_path(): void
    {
        $expected = 'Foo.php';

        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $fileInfoMock
            ->method('getRealPath')
            ->willReturn($expected)
        ;

        $actual = (new ProxyTrace($fileInfoMock, []))->getRealPath();

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_retrieve_the_test_locations(): void
    {
        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $tests = new TestLocations();

        $trace = new ProxyTrace($fileInfoMock, [$tests]);

        $actual = $trace->getTests();
        $this->assertSame($tests, $actual);

        // From cache
        $actual = $trace->getTests();
        $this->assertSame($tests, $actual);
    }

    public function test_it_has_no_tests_if_no_covered(): void
    {
        $fileInfoMock = $this->createMock(SplFileInfo::class);

        $trace = new ProxyTrace($fileInfoMock, [new TestLocations()]);

        $this->assertFalse($trace->hasTests());
    }

    public function test_it_exposes_its_test_locations(): void
    {
        $fileInfoMock = $this->createMock(SplFileInfo::class);

        $tests = new TestLocations(
            [
                21 => [
                    TestLocation::forTestMethod('Acme\FooTest::test_it_can_be_instantiated'),
                ],
            ],
            [
                '__construct' => new SourceMethodLineRange(
                    19,
                    22
                ),
            ]
        );

        $trace = new ProxyTrace($fileInfoMock, [$tests]);

        $this->assertTrue($trace->hasTests());

        // More extensive tests done on the ability to locate the tests are done in the TestLocator
        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(1, 1),
                false
            )
        );

        $this->assertCount(
            1,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(20, 21),
                false
            )
        );

        // This iterator_to_array is due to bug in our version of PHPUnit
        $this->assertCount(
            0,
            iterator_to_array($trace->getAllTestsForMutation(
                new NodeLineRangeData(1, 1),
                true
            ))
        );

        $this->assertCount(
            1,
            iterator_to_array($trace->getAllTestsForMutation(
                new NodeLineRangeData(19, 22),
                true
            ))
        );
    }
}
