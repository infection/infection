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

use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageMethodData;
use Infection\TestFramework\Coverage\TestFileDataProvider;
use Infection\TestFramework\Coverage\TestFileTimeData;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\TestFrameworkTypes;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CodeCoverageDataTest extends TestCase
{
    private $coverageDir = __DIR__ . '/../../Fixtures/Files/phpunit/coverage-xml';

    public function test_it_correctly_sets_coverage_information_for_method_body(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $coverageOfLine = $codeCoverageData->getAllTestsForMutation($filePath, [34], false);
        $this->assertCount(1, $coverageOfLine);
        $this->assertSame(0.123, $coverageOfLine[0]->time);
        $this->assertSame('path/to/testFile', $coverageOfLine[0]->testFilePath);
        $this->assertSame(
            'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
            $coverageOfLine[0]->testMethod
        );
    }

    public function test_it_correctly_sets_coverage_information_for_method_signature(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $coverageOfLine = $codeCoverageData->getAllTestsForMutation($filePath, [24], true);

        $this->assertCount(6, $codeCoverageData->getAllTestsForMutation($filePath, [24], true));
        $this->assertSame(0.123, $coverageOfLine[0]->time);
        $this->assertSame('path/to/testFile', $coverageOfLine[0]->testFilePath);
        $this->assertSame(
            'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
            $coverageOfLine[0]->testMethod
        );
    }

    public function test_it_determines_method_was_not_executed_from_coverage_report(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [19], true));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [21], false));
    }

    public function test_it_determines_line_was_not_executed_from_coverage_report(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [27], false));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [32], false));
    }

    public function test_it_determines_file_is_not_covered_for_unknown_path(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = 'unknown/path';

        $this->assertFalse($codeCoverageData->hasTests($filePath));
    }

    public function test_it_determines_file_is_covered(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertTrue($codeCoverageData->hasTests($filePath));
    }

    public function test_it_determines_file_is_not_covered(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevelNotCovered.php';

        $this->assertFalse($codeCoverageData->hasTests($filePath));
    }

    public function test_it_determines_file_does_not_have_tests_on_line_for_unknown_file(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = 'unknown/path';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [34], true));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [34], false));
    }

    public function test_it_determines_file_does_not_have_tests_for_line(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [1], true));
        $this->assertEmpty($codeCoverageData->getAllTestsForMutation($filePath, [1], false));
    }

    public function test_it_returns_zero_tests_for_not_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertCount(0, $codeCoverageData->getAllTestsForMutation($filePath, [1], false));
    }

    public function test_it_returns_tests_for_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $tests = $codeCoverageData->getAllTestsForMutation($filePath, [26], false);

        $this->assertCount(2, $tests);
        $this->assertSame('path/to/testFile', $tests[0]->testFilePath);
        $this->assertSame(0.123, $tests[0]->time);
    }

    public function test_it_returns_zero_tests_for_not_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertCount(0, $codeCoverageData->getAllTestsForMutation($filePath, [1], true));
    }

    public function test_it_returns_tests_for_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertCount(6, $codeCoverageData->getAllTestsForMutation($filePath, [24], true));
    }

    public function test_it_throws_an_exception_when_no_coverage_found(): void
    {
        $coverageXmlParserMock = $this->createMock(CoverageXmlParser::class);

        $coverage = new CodeCoverageData('/abc/foo/bar', $coverageXmlParserMock, TestFrameworkTypes::PHPUNIT);

        $this->expectException(CoverageDoesNotExistException::class);
        $this->expectExceptionMessage(
            'Code Coverage does not exist. File /abc/foo/bar/index.xml is not found. ' .
            'Check phpunit version Infection was run with and generated config files inside /abc/foo.'
        );
        $coverage->hasTests('/abc/def.php');
    }

    private function getParsedCodeCoverageData(): array
    {
        return [
            '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php' => new CoverageFileData(
                [
                    26 => [
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'),
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'),
                    ],
                    30 => [
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'),
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'),
                    ],
                    31 => [
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'),
                    ],
                    34 => [
                        CoverageLineData::withTestMethod('Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'),
                    ],
                ],
                [
                    'mutate' => new CoverageMethodData(
                        19,
                        22
                    ),
                    'shouldMutate' => new CoverageMethodData(
                        24,
                        35
                    ),
                    'notExecuted' => new CoverageMethodData(
                        3,
                        5
                    ),
                ]
            ),
        ];
    }

    private function getCodeCoverageData(): CodeCoverageData
    {
        $coverageXmlParserMock = $this->createMock(CoverageXmlParser::class);
        $coverageXmlParserMock->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData());

        $testFileDataProvider = $this->createMock(TestFileDataProvider::class);
        $testFileDataProvider->expects($this->any())
            ->method('getTestFileInfo')
            ->willReturn(
                new TestFileTimeData(
                    'path/to/testFile',
                    0.123
                )
            );

        return new CodeCoverageData(
            $this->coverageDir,
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT,
            $testFileDataProvider
        );
    }
}
