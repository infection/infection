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
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\PhpUnit\Coverage\Exception\NoLineExecuted;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\preg_replace;
use function Safe\realpath;
use function Safe\sprintf;

final class CoverageXmlParserTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage-xml';

    /**
     * @var string|null
     */
    private static $xml;

    /**
     * @var CoverageXmlParser
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
        $this->parser = new CoverageXmlParser();
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

        $zeroLevelAbsolutePath = realpath(self::FIXTURES_DIR . '/zeroLevel.php');
        $firstLevelAbsolutePath = realpath(self::FIXTURES_DIR . '/FirstLevel/firstLevel.php');
        $secondLevelAbsolutePath = realpath(self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevel.php');

        $this->assertArrayHasKey($zeroLevelAbsolutePath, $coverage);
        $this->assertArrayHasKey($firstLevelAbsolutePath, $coverage);
        $this->assertArrayHasKey($secondLevelAbsolutePath, $coverage);

        $this->assertCount(0, $coverage[$zeroLevelAbsolutePath]->byLine);
        $this->assertCount(4, $coverage[$firstLevelAbsolutePath]->byLine);
        $this->assertCount(1, $coverage[$secondLevelAbsolutePath]->byLine);

        $this->assertSame(
            'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
            $coverage[$firstLevelAbsolutePath]->byLine[30][1]->testMethod
        );
    }

    public function test_it_adds_by_method_coverage_data(): void
    {
        $firstLevelAbsolutePath = realpath(self::FIXTURES_DIR . '/FirstLevel/firstLevel.php');

        $expectedByMethodArray = [
            'mutate' => [
                'startLine' => 19,
                'endLine' => 22,
            ],
            'shouldMutate' => [
                'startLine' => 24,
                'endLine' => 35,
            ],
        ];

        $coverage = $this->parser->parse(self::$xml);

        $result = self::convertObjectsToArray($coverage[$firstLevelAbsolutePath]->byMethod);

        $this->assertSame($expectedByMethodArray, $result);
    }

    public function test_it_adds_by_method_coverage_data_for_traits(): void
    {
        $pathToTrait = realpath(self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevelTrait.php');

        $expectedByMethodArray = [
            'mutate' => [
                'startLine' => 19,
                'endLine' => 22,
            ],
            'shouldMutate' => [
                'startLine' => 24,
                'endLine' => 35,
            ],
        ];

        $coverage = $this->parser->parse(self::$xml);

        $result = self::convertObjectsToArray($coverage[$pathToTrait]->byMethod);

        $this->assertSame($expectedByMethodArray, $result);
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

    private static function convertObjectsToArray(array $coverageMethodsData): array
    {
        $result = [];

        foreach ($coverageMethodsData as $method => $coverageMethodData) {
            $result[$method] = (array) $coverageMethodData;
        }

        return $result;
    }
}
