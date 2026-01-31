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

use Exception;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Tracing\Trace\ProxyTrace;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\Tests\TestFramework\Tracing\Trace\FakeTrace;
use Infection\Tests\TestFramework\Tracing\Trace\TraceAssertion;
use function iterator_to_array;
use function Later\lazy;
use function Later\now;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(JUnitTestExecutionInfoAdder::class)]
final class JUnitTestExecutionInfoAdderTest extends TestCase
{
    private TestFrameworkAdapter&MockObject $testFrameworkAdapterMock;

    private TestFileDataProvider&MockObject $testFileDataProviderMock;

    private JUnitTestExecutionInfoAdder $infoAdder;

    protected function setUp(): void
    {
        $this->testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $this->testFileDataProviderMock = $this->createMock(TestFileDataProvider::class);

        $this->infoAdder = new JUnitTestExecutionInfoAdder(
            $this->testFrameworkAdapterMock,
            $this->testFileDataProviderMock,
        );
    }

    public function test_it_does_not_add_if_junit_is_not_provided(): void
    {
        $this->testFrameworkAdapterMock
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn(false)
        ;

        $this->testFileDataProviderMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->infoAdder->addTestExecutionInfo([new FakeTrace()]);
    }

    public function test_it_adds_if_junit_is_provided(): void
    {
        $this->testFrameworkAdapterMock
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn(true)
        ;

        $this->testFileDataProviderMock
            ->expects($this->once())
            ->method('getTestFileInfo')
            ->with('Acme\FooTest')
            ->willReturn(new TestFileTimeData(
                '/path/to/acme/FooTest.php',
                0.000234,
            ))
        ;

        $tests = new TestLocations(
            [
                11 => [
                    TestLocation::forTestMethod('Acme\FooTest::test_it_can_be_instantiated'),
                ],
            ],
            [],
        );

        $sourceFile = new SplFileInfo(__FILE__);

        $proxyTrace = new ProxyTrace(
            $sourceFile,
            '',
            now($tests),
        );

        $expected = new ProxyTrace(
            $sourceFile,
            '',
            now(
                new TestLocations(
                    [
                        11 => [
                            new TestLocation(
                                'Acme\FooTest::test_it_can_be_instantiated',
                                '/path/to/acme/FooTest.php',
                                0.000234,
                            ),
                        ],
                    ],
                    [],
                ),
            ),
        );

        $completedTraces = iterator_to_array($this->infoAdder->addTestExecutionInfo([$proxyTrace]), false);

        $this->assertCount(1, $completedTraces);
        $this->assertArrayHasKey(0, $completedTraces);

        $actual = $completedTraces[0];

        TraceAssertion::assertEquals($expected, $actual);
    }

    public function test_it_does_not_load_the_trace_tests_until_necessary(): void
    {
        $this->testFrameworkAdapterMock
            ->expects($this->once())
            ->method('hasJUnitReport')
            ->willReturn(true)
        ;

        $sourceFile = new SplFileInfo(__FILE__);

        $proxyTrace = new ProxyTrace(
            $sourceFile,
            '',
            // @phpstan-ignore argument.templateType,argument.type,callable.void
            lazy((static function () {
                throw new Exception();

                // We need to include a yield statement to make it a generator even though
                // it is not reachable.
                // @phpstan-ignore deadCode.unreachable
                yield new TestLocations();
            })()),
        );

        $completedTraces = iterator_to_array($this->infoAdder->addTestExecutionInfo([$proxyTrace]), false);

        $this->assertCount(1, $completedTraces);
        $this->assertArrayHasKey(0, $completedTraces);

        /** @var Trace $actual */
        $actual = $completedTraces[0];

        $this->expectException(Exception::class);

        $actual->getTests();
    }
}
