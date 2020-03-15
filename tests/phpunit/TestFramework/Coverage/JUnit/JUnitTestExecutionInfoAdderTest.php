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

namespace Infection\Tests\TestFramework\Coverage\JUnit;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Coverage\ProxyTrace;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\Tests\TestFramework\Coverage\TestLocationsNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class JUnitTestExecutionInfoAdderTest extends TestCase
{
    public function test_it_does_not_add_if_junit_is_not_provided(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn(false)
        ;

        $testFileDataProvider = $this->createMock(TestFileDataProvider::class);
        $testFileDataProvider
            ->expects($this->never())
            ->method($this->anything())
        ;

        $adder = new JUnitTestExecutionInfoAdder($adapter, $testFileDataProvider);

        $proxyTraceMock = $this->createMock(ProxyTrace::class);
        $proxyTraceMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $adder->addTestExecutionInfo([$proxyTraceMock]);
    }

    public function test_it_adds_if_junit_is_provided(): void
    {
        $adapter = $this->createMock(TestFrameworkAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn(true)
        ;

        $testFileDataProvider = $this->createMock(TestFileDataProvider::class);
        $testFileDataProvider
            ->expects($this->once())
            ->method('getTestFileInfo')
            ->with('Acme\FooTest')
            ->willReturn(new TestFileTimeData(
                '/path/to/acme/FooTest.php',
                0.000234
            ))
        ;

        $adder = new JUnitTestExecutionInfoAdder($adapter, $testFileDataProvider);

        $tests = new TestLocations(
            [
                11 => [
                    TestLocation::forTestMethod('Acme\FooTest::test_it_can_be_instantiated'),
                ],
            ],
            []
        );

        $proxyTrace = new ProxyTrace(
            new SplFileInfo('/path/to/Foo.php', 'Foo.php', 'Foo.php'),
            [$tests]
        );

        $expected = [$proxyTrace];

        $actual = $adder->addTestExecutionInfo($expected);
        $actual = iterator_to_array($actual, false);

        $this->assertSame($expected, $actual);

        $this->assertSame(
            [
                [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => '/path/to/acme/FooTest.php',
                                'testExecutionTime' => 0.000234,
                            ],
                        ],
                    ],
                    'byMethod' => [],
                ],
            ],
            TestLocationsNormalizer::normalize([$proxyTrace->retrieveTestLocations()])
        );
    }
}
