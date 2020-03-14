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
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\XmlReport\TestTrace;
use Infection\Tests\TestFramework\Coverage\TestLocationsNormalizer;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use Traversable;

final class TestTraceTest extends TestCase
{
    private static $testsLocations;

    public function test_it_correctly_sets_coverage_information_for_method_body(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $tests = $this->createTestTrace($filePath)->getAllTestsForMutation(
            new NodeLineRangeData(34, 34),
            false
        );

        $this->assertSame(
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
            ],
            TestLocationsNormalizer::normalize($tests)
        );
    }

    public function test_it_correctly_sets_coverage_information_for_method_signature(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $tests = $this->createTestTrace($filePath)->getAllTestsForMutation(
            new NodeLineRangeData(24, 24),
            true
        );

        $this->assertSame(
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
            ],
            TestLocationsNormalizer::normalize($tests)
        );
    }

    public function test_it_determines_method_was_not_executed_from_coverage_report(): void
    {
        $filePath = '/path/to/acme/Foo.php';
        $trace = $this->createTestTrace($filePath);

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(19, 19),
                true
            )
        );

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(21, 21),
                false
            )
        );
    }

    public function test_it_determines_line_was_not_executed_from_coverage_report(): void
    {
        $filePath = '/path/to/acme/Foo.php';
        $trace = $this->createTestTrace($filePath);

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(27, 27),
                false
            )
        );

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(32, 32),
                false
            )
        );
    }

    public function test_it_determines_file_is_not_covered_for_unknown_path(): void
    {
        $filePath = '/path/to/unknown-file';

        $this->assertFalse($this->createTestTrace($filePath)->hasTests());
    }

    public function test_it_determines_file_is_covered(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $this->assertTrue($this->createTestTrace($filePath)->hasTests());
    }

    public function test_it_determines_file_does_not_have_tests_on_line_for_unknown_file(): void
    {
        $filePath = '/path/to/unknown-file';
        $trace = $this->createTestTrace($filePath);

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(34, 34),
                true
            )
        );

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(34, 34),
                false
            )
        );
    }

    public function test_it_determines_file_does_not_have_tests_for_line(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $trace = $this->createTestTrace($filePath);

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(1, 1),
                true
            )
        );

        $this->assertCount(
            0,
            $trace->getAllTestsForMutation(
                new NodeLineRangeData(1, 1),
                false
            )
        );
    }

    public function test_it_returns_zero_tests_for_not_covered_function_body_mutator(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $this->assertCount(
            0,
            $this->createTestTrace($filePath)->getAllTestsForMutation(
                new NodeLineRangeData(1, 1),
                false
            )
        );
    }

    public function test_it_returns_tests_for_covered_function_body_mutator(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $tests = $this->createTestTrace($filePath)->getAllTestsForMutation(
            new NodeLineRangeData(26, 26),
            false
        );

        if ($tests instanceof Traversable) {
            $tests = iterator_to_array($tests, true);
        }

        $this->assertSame(
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.456,
                ],
            ],
            TestLocationsNormalizer::normalize($tests)
        );
    }

    public function test_it_returns_zero_tests_for_not_covered_function_signature_mutator(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $this->assertCount(
            0,
            $this->createTestTrace($filePath)->getAllTestsForMutation(
                new NodeLineRangeData(1, 1), true
            )
        );
    }

    public function test_it_returns_tests_for_covered_function_signature_mutator(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $tests = $this->createTestTrace($filePath)->getAllTestsForMutation(
            new NodeLineRangeData(24, 24),
            true
        );

        $this->assertCount(6, $tests);
    }

    private function createTestTrace(string $filePath): TestTrace
    {
        $testsLocations = $this->getTestsLocations();

        if (!array_key_exists($filePath, $testsLocations)) {
            return new TestTrace(new TestLocations());
        }

        return new TestTrace($testsLocations[$filePath]);
    }

    private function getTestsLocations(): array
    {
        return self::$testsLocations ?? [
            '/path/to/acme/Foo.php' => new TestLocations(
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
                    'do0' => new MethodLocationData(19, 22),
                    'do1' => new MethodLocationData(24, 35),
                    'doSomethingUncovered' => new MethodLocationData(3, 5),
                ]
            ),
        ];
    }
}
