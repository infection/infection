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
use Infection\TestFramework\Coverage\PhpUnit\PhpUnitXmlCoverageFactory;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\TestFramework\Coverage\CoverageHelper;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

final class PhpUnitXmlCoverageFactoryTest extends TestCase
{
    private const COVERAGE_DIR = __DIR__ . '/../../Fixtures/Files/phpunit/coverage/coverage-xml';

    /**
     * @var PhpUnitXmlCoverageFactory
     */
    private $coverageFactory;

    public function test_it_can_parse_and_enrich_the_coverage_data(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $coverageXmlParserMock
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        $testFileDataProvider = $this->createMock(TestFileDataProvider::class);
        $testFileDataProvider
            ->expects($this->any())
            ->method('getTestFileInfo')
            ->with('Acme\FooTest')
            ->willReturn(
                new TestFileTimeData(
                    '/path/to/acme/FooTest.php',
                    0.000234
                )
            )
        ;

        $coverageFactory = new PhpUnitXmlCoverageFactory(
            realpath(self::COVERAGE_DIR),
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT,
            $testFileDataProvider
        );

        $coverage = $coverageFactory->createCoverage();

        $this->assertSame(
            [
                '/path/to/acme/Foo.php' => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Acme\FooTest::test_it_can_be_instantiated',
                                'testFilePath' => '/path/to/acme/FooTest.php',
                                'time' => 0.000234,
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

    public function test_it_cannot_create_coverage_if_cannot_locate_the_coverage_index_file(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $testFileDataProvider = $this->createMock(TestFileDataProvider::class);

        $coverageFactory = new PhpUnitXmlCoverageFactory(
            '/nowhere',
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT,
            $testFileDataProvider
        );

        try {
            $coverageFactory->createCoverage();

            $this->fail();
        } catch (CoverageDoesNotExistException $exception) {
            $this->assertSame(
                <<<'TXT'
Code Coverage does not exist. File /nowhere/index.xml is not found. Check phpunit version Infection was run with and generated config files inside /. Make sure to either:
- Enable xdebug and run infection again
- Use phpdbg: phpdbg -qrr infection
- Enable pcov and run infection again
- Use --coverage option with path to the existing coverage report
- Use --initial-tests-php-options option with `-d zend_extension=xdebug.so` and/or any extra php parameters
TXT
                ,
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function test_it_does_not_add_test_file_info_if_not_provider_is_given(): void
    {
        $coverageXmlParserMock = $this->createMock(IndexXmlCoverageParser::class);
        $coverageXmlParserMock
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        $coverageFactory = new PhpUnitXmlCoverageFactory(
            realpath(self::COVERAGE_DIR),
            $coverageXmlParserMock,
            TestFrameworkTypes::PHPUNIT,
            null
        );

        $coverage = $coverageFactory->createCoverage();

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

    private function getParsedCodeCoverageData(): array
    {
        return [
            '/path/to/acme/Foo.php' => new CoverageFileData(
                [
                    11 => [
                        CoverageLineData::withTestMethod('Acme\FooTest::test_it_can_be_instantiated'),
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
