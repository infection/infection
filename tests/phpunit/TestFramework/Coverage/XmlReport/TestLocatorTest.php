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
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\XmlReport\TestLocator;
use Infection\Tests\TestFramework\Coverage\TestLocationsNormalizer;
use PHPUnit\Framework\TestCase;

final class TestLocatorTest extends TestCase
{
    /**
     * @var array<string, TestLocations>|null
     */
    private static $testsLocations;

    public function test_it_can_determine_if_the_file_is_tested(): void
    {
        $testLocator = $this->createTestLocator('/path/to/unknown-file');

        $this->assertFalse($testLocator->hasTests());
    }

    public function test_it_can_determine_if_the_file_is_not_tested(): void
    {
        $testLocator = $this->createTestLocator('/path/to/acme/Foo.php');

        $this->assertTrue($testLocator->hasTests());
    }

    /**
     * @dataProvider rangeProvider
     *
     * @param array<string, string|float>[] $expectedTests
     */
    public function test_it_can_locate_the_tests_executing_the_given_range(
        NodeLineRangeData $range,
        bool $onFunctionSignature,
        array $expectedTests
    ): void {
        $testLocator = $this->createTestLocator('/path/to/acme/Foo.php');

        $tests = $testLocator->getAllTestsForMutation($range, $onFunctionSignature);

        $this->assertSame($expectedTests, TestLocationsNormalizer::normalize($tests));
    }

    /**
     * @dataProvider rangeProvider
     */
    public function test_it_cannot_locate_any_tests_executing_the_given_range_if_no_tests_are_found(
        NodeLineRangeData $range,
        bool $onFunctionSignature
    ): void {
        $testLocator = $this->createTestLocator('/path/to/unknown-file');

        $this->assertFalse($testLocator->hasTests());

        $tests = $testLocator->getAllTestsForMutation($range, $onFunctionSignature);

        $this->assertSame([], TestLocationsNormalizer::normalize($tests));
    }

    public function rangeProvider(): iterable
    {
        yield 'executed body' => [
            new NodeLineRangeData(34, 34),
            false,
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'testExecutionTime' => 0.123,
                ],
            ],
        ];

        yield 'executed function signature' => [
            new NodeLineRangeData(24, 24),
            true,
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
        ];

        yield 'non executed body' => [
            new NodeLineRangeData(21, 21),
            false,
            [],
        ];

        yield 'non executed function signature' => [
            new NodeLineRangeData(19, 19),
            true,
            [],
        ];

        yield 'non executed line' => [
            new NodeLineRangeData(1, 1),
            false,
            [],
        ];

        yield 'non executed function signature line' => [
            new NodeLineRangeData(1, 1),
            true,
            [],
        ];
    }

    private function createTestLocator(string $filePath): TestLocator
    {
        $testsLocations = $this->getTestsLocations();

        if (!array_key_exists($filePath, $testsLocations)) {
            return new TestLocator(new TestLocations());
        }

        return new TestLocator($testsLocations[$filePath]);
    }

    private function getTestsLocations(): array
    {
        return self::$testsLocations ?? self::$testsLocations = [
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
                    'do0' => new SourceMethodLineRange(19, 22),
                    'do1' => new SourceMethodLineRange(24, 35),
                    'doSomethingUncovered' => new SourceMethodLineRange(3, 5),
                ]
            ),
        ];
    }
}
