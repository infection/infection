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

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

use Generator;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use Infection\TestFramework\PhpUnit\Coverage\NoLineExecuted;
use function is_array;
use function is_scalar;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\preg_replace;
use function Safe\realpath;
use function Safe\sprintf;

final class IndexXmlCoverageParserTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage-xml';

    /**
     * @var string|null
     */
    private static $xml;

    /**
     * @var IndexXmlCoverageParser
     */
    private $parser;

    public static function setUpBeforeClass(): void
    {
        $xml = file_get_contents(self::FIXTURES_DIR . '/index.xml');

        // Replaces dummy source path with the real path
        self::$xml = preg_replace(
            '/(source=\").*?(\")/',
            sprintf('$1%s$2', realpath(self::FIXTURES_DIR)),
            $xml
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$xml = null;
    }

    protected function setUp(): void
    {
        $this->parser = new IndexXmlCoverageParser();
    }

    public function test_it_collects_data_recursively_for_all_files(): void
    {
        $coverage = $this->parser->parse(self::$xml);

        // zeroLevel / firstLevel / secondLevel
        $this->assertCount(4, $coverage);
    }

    public function test_it_has_correct_coverage_data_for_each_file(): void
    {
        $coverage = $this->parser->parse(self::$xml);

        $zeroLevelPath = realpath(self::FIXTURES_DIR . '/zeroLevel.php');
        $firstLevelPath = realpath(self::FIXTURES_DIR . '/FirstLevel/firstLevel.php');
        $secondLevelPath = realpath(self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevel.php');
        $secondLevelTraitPath = realpath(self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevelTrait.php');

        $this->assertSame(
            [
                $firstLevelPath => [
                    'byLine' => [
                        26 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                        30 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                        31 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                        34 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                        'shouldMutate' => [
                            'startLine' => 24,
                            'endLine' => 35,
                        ],
                    ],
                ],
                $secondLevelPath => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                        'shouldMutate' => [
                            'startLine' => 24,
                            'endLine' => 35,
                        ],
                    ],
                ],
                $secondLevelTraitPath => [
                    'byLine' => [
                        11 => [
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                            [
                                'testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                                'testFilePath' => null,
                                'time' => null,
                            ],
                        ],
                    ],
                    'byMethod' => [
                        'mutate' => [
                            'startLine' => 19,
                            'endLine' => 22,
                        ],
                        'shouldMutate' => [
                            'startLine' => 24,
                            'endLine' => 35,
                        ],
                    ],
                ],
                $zeroLevelPath => [
                    'byLine' => [],
                    'byMethod' => [],
                ],
            ],
            self::convertCoverageToArray($coverage)
        );
    }

    public function test_it_correctly_parses_xml_when_directory_has_absolute_path_for_old_phpunit_versions(): void
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/absolute/path">
      <totals>
        <lines total="913" comments="130" code="783" executable="348" executed="7" percent="0"/>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML;

        $coverage = $this->parser->parse($xml);

        $this->assertSame([], $coverage);
    }

    /**
     * @dataProvider noCoveredLineReportProviders
     */
    public function test_it_errors_when_no_lines_were_executed(string $xml): void
    {
        $this->expectException(NoLineExecuted::class);

        $this->parser->parse($xml);
    }

    public function noCoveredLineReportProviders(): Generator
    {
        yield 'zero lines executed' => [<<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/">
      <totals>
        <lines total="913" comments="130" code="783" executable="348" executed="0" percent="0"/>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML
            ];

        yield 'lines is not present' => [<<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/">
      <totals>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML
        ];
    }

    /**
     * @param array<string, CoverageFileData> $coverage
     *
     * @return array<string, mixed>
     */
    private static function convertCoverageToArray(array $coverage): array
    {
        return self::serializeValue($coverage);
    }

    private static function serializeValue($mixed)
    {
        if ($mixed === null) {
            return null;
        }

        if (is_scalar($mixed)) {
            return $mixed;
        }

        if (is_array($mixed)) {
            $convertedArray = [];

            foreach ($mixed as $key => $value) {
                $convertedArray[$key] = self::serializeValue($value);
            }

            return $convertedArray;
        }

        return self::serializeValue((array) $mixed);
    }
}
