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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\ProxyTrace;
use Infection\TestFramework\Coverage\SourceMethodRange;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\XmlReport\TestTraceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class TestTraceProviderTest extends TestCase
{
    public function test_it_determines_if_trace_is_covered(): void
    {
        $trace = (new TestTraceProvider())->provideFor($this->createProxyTrace());

        $this->assertTrue($trace->hasTests());
    }

    private function getParsedTestLocations(): TestLocations
    {
        return new TestLocations(
            [
                26 => [
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_0',
                        '/path/to/acme/FooTest.php',
                        0.123
                    ),
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_1',
                        '/path/to/acme/FooTest.php',
                        0.456
                    ),
                ],
                30 => [
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_0',
                        '/path/to/acme/FooTest.php',
                        0.123
                    ),
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_1',
                        '/path/to/acme/FooTest.php',
                        0.456
                    ),
                ],
                31 => [
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_1',
                        '/path/to/acme/FooTest.php',
                        0.456
                    ),
                ],
                34 => [
                    new TestLocation(
                        'Infection\\Acme\\FooTest::test_it_can_do_0',
                        '/path/to/acme/FooTest.php',
                        0.123
                    ),
                ],
            ],
            [
                'do0' => new SourceMethodRange(19, 22),
                'do1' => new SourceMethodRange(24, 35),
                'doSomethingUncovered' => new SourceMethodRange(3, 5),
            ]
        );
    }

    private function createProxyTrace(): ProxyTrace
    {
        return new ProxyTrace(
            $this->createFileInfoMock(),
            [$this->getParsedTestLocations()]
        );
    }

    private function createFileInfoMock(): SplFileInfo
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock
            ->expects($this->never())
            ->method('getRealPath')
        ;

        $splFileInfoMock
            ->expects($this->never())
            ->method('getPathname')
        ;

        return $splFileInfoMock;
    }
}
