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

use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoveredFileDataProvider;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\TestFramework\Coverage\CoverageHelper;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

/**
 * @group integration
 */
final class PhpUnitXmlCoveredFileDataProviderTest extends TestCase
{
    private const COVERAGE_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage/coverage-xml';

    public function test_it_can_parse_and_enrich_the_coverage_data(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $coverageXmlParserMock
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        $coverageProvider = new PhpUnitXmlCoveredFileDataProvider(
            realpath(self::COVERAGE_DIR),
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT
        );

        $coverage = $coverageProvider->provideFiles();

        $this->assertSame(
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        '__construct' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                    ],
                ],
            ],
            CoverageHelper::convertToArray($coverage)
        );
    }

    public function test_it_can_parse_codeception_cest_coverage(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $coverageXmlParserMock
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData('Acme\FooCest:test_it_can_be_instantiated'))
        ;

        $coverageProvider = new PhpUnitXmlCoveredFileDataProvider(
            realpath(self::COVERAGE_DIR),
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT
        );

        $coverage = $coverageProvider->provideFiles();

        $this->assertSame(
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooCest:test_it_can_be_instantiated',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        '__construct' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                    ],
                ],
            ],
            CoverageHelper::convertToArray($coverage)
        );
    }

    public function test_it_does_not_add_test_file_info_if_not_provider_is_given(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $coverageXmlParserMock
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        $coverageProvider = new PhpUnitXmlCoveredFileDataProvider(
            realpath(self::COVERAGE_DIR),
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT
        );

        $coverage = $coverageProvider->provideFiles();

        $this->assertSame(
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        '__construct' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                    ],
                ],
            ],
            CoverageHelper::convertToArray($coverage)
        );
    }

    private function getParsedCodeCoverageData(string $testMethod = 'Acme\FooTest::test_it_can_be_instantiated'): array
    {
        return [
            '/path/to/acme/Foo.php' => new CoverageFileData(
                [
                    11 => [
                        CoverageLineData::withTestMethod($testMethod),
                    ],
                ],
                [
                    '__construct' => new MethodLocationData(
                        19,
                        22
                    ),
                ]
            ),
        ];
    }
}
