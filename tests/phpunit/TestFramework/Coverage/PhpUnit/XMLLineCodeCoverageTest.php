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

namespace Infection\Tests\TestFramework\Coverage\PhpUnit;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\Tests\TestFramework\Coverage\CoverageHelper;
use PHPUnit\Framework\TestCase;

final class XMLLineCodeCoverageTest extends TestCase
{
    public function test_it_correctly_sets_coverage_information_for_method_body(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $tests = $codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(34, 34),
            false
        );

        $this->assertSame(
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.123,
                ],
            ],
            CoverageHelper::convertToArray($tests)
        );
    }

    public function test_it_correctly_sets_coverage_information_for_method_signature(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $tests = $codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(24, 24),
            true
        );

        $this->assertSame(
            [
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.123,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.123,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_1',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.456,
                ],
                [
                    'testMethod' => 'Infection\Acme\FooTest::test_it_can_do_0',
                    'testFilePath' => '/path/to/acme/FooTest.php',
                    'time' => 0.123,
                ],
            ],
            CoverageHelper::convertToArray($tests)
        );
    }

    public function test_it_determines_method_was_not_executed_from_coverage_report(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(19, 19),
            true
        ));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(21, 21),
            false
        ));
    }

    public function test_it_determines_line_was_not_executed_from_coverage_report(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(27, 27),
            false
        ));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath, new NodeLineRangeData(32, 32), false
        ));
    }

    public function test_it_determines_file_is_not_covered_for_unknown_path(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/unknown-file';

        $this->assertFalse($codeCoverageData->hasTests($filePath));
    }

    public function test_it_determines_file_is_covered(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertTrue($codeCoverageData->hasTests($filePath));
    }

    public function test_it_determines_file_does_not_have_tests_on_line_for_unknown_file(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/unknown-file';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(34, 34),
            true
        ));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(34, 34),
            false
        ));
    }

    public function test_it_determines_file_does_not_have_tests_for_line(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(1, 1),
            true
        ));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(1, 1),
            false
        ));
    }

    public function test_it_returns_zero_tests_for_not_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertCount(0, $codeCoverageData->getAllTestsForMutation($filePath,
            new NodeLineRangeData(1, 1),
            false
        ));
    }

    public function test_it_returns_tests_for_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $tests = $codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(26, 26),
            false
        );

        $this->assertCount(2, $tests);
        $this->assertSame('/path/to/acme/FooTest.php', $tests[0]->testFilePath);
        $this->assertSame(0.123, $tests[0]->time);
    }

    public function test_it_returns_zero_tests_for_not_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(1, 1), true
        ));
    }

    public function test_it_returns_tests_for_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->createCodeCoverageData();
        $filePath = '/path/to/acme/Foo.php';

        $tests = $codeCoverageData->getAllTestsForMutation(
            $filePath,
            new NodeLineRangeData(24, 24),
            true
        );

        $this->assertCount(6, $tests);
    }

    public function test_it_throws_an_exception_when_no_coverage_found(): void
    {
        $coverageFactoryMock = $this->createMock(PhpUnitXmlCoverageFactory::class);
        $coverageFactoryMock
            ->expects($this->once())
            ->method('createCoverage')
            ->willThrowException($exception = new CoverageDoesNotExistException())
        ;

        $coverage = new XMLLineCodeCoverage($coverageFactoryMock);

        try {
            $coverage->hasTests('/path/to/random-file');

            $this->fail();
        } catch (CoverageDoesNotExistException $caughtException) {
            $this->assertSame($exception, $caughtException);
        }
    }

    private function getParsedCodeCoverageData(): array
    {
        return [
            '/path/to/acme/Foo.php' => new CoverageFileData(
                [
                    26 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_0',
                            '/path/to/acme/FooTest.php',
                            0.123
                        ),
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    30 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_0',
                            '/path/to/acme/FooTest.php',
                            0.123
                        ),
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    31 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    34 => [
                        CoverageLineData::with(
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

    private function createCodeCoverageData(): XMLLineCodeCoverage
    {
        $coverageFactoryMock = $this->createMock(PhpUnitXmlCoverageFactory::class);
        $coverageFactoryMock
            ->expects($this->once())
            ->method('createCoverage')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        return new XMLLineCodeCoverage($coverageFactoryMock);
    }
}
