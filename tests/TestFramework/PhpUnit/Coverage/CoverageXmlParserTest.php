<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;


use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use PHPUnit\Framework\TestCase;

class CoverageXmlParserTest extends TestCase
{
    /**
     * @var CoverageXmlParser
     */
    private $parser;

    private $tempDir = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage-xml';

    private $srcDir = __DIR__ . '/../../../Fixtures/Files/phpunit/coverage-xml';

    protected function setUp()
    {
        $this->parser = new CoverageXmlParser($this->tempDir);
    }

    protected function getXml()
    {
        $xml = file_get_contents(__DIR__ . '/../../../Fixtures/Files/phpunit/coverage-xml/index.xml');

        // replace dummy source path with the real path
        return preg_replace(
            '/(source=\").*?(\")/',
            sprintf('$1%s$2', realpath($this->srcDir)),
            $xml
        );
    }

    public function test_it_collects_data_recursively_for_all_files()
    {
        $coverage = $this->parser->parse($this->getXml());

        // zeroLevel / firstLevel / secondLevel
        $this->assertCount(3, $coverage);
    }

    public function test_it_has_correct_coverage_data_for_each_file()
    {
        $coverage = $this->parser->parse($this->getXml());

        $zeroLevelAbsolutePath = realpath($this->tempDir . '/zeroLevel.php');
        $firstLevelAbsolutePath = realpath($this->tempDir . '/FirstLevel/firstLevel.php');
        $secondLevelAbsolutePath = realpath($this->tempDir . '/FirstLevel/SecondLevel/secondLevel.php');

        $this->assertArrayHasKey($zeroLevelAbsolutePath, $coverage);
        $this->assertArrayHasKey($firstLevelAbsolutePath, $coverage);
        $this->assertArrayHasKey($secondLevelAbsolutePath, $coverage);

        $this->assertCount(0, $coverage[$zeroLevelAbsolutePath]['byLine']);
        $this->assertCount(4, $coverage[$firstLevelAbsolutePath]['byLine']);
        $this->assertCount(1, $coverage[$secondLevelAbsolutePath]['byLine']);

        $this->assertSame(
            ['testMethod' => 'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
            $coverage[$firstLevelAbsolutePath]['byLine'][30][1]
        );
    }

    public function test_it_adds_by_method_coverage_data()
    {
        $firstLevelAbsolutePath = realpath($this->tempDir . '/FirstLevel/firstLevel.php');
        $expectedByMethodArray = [
            'mutate' => [
                'startLine' => 19,
                'endLine' => 22,
                'executable' => 1,
                'executed' => 0,
                'coverage' => 0,
            ],
            'shouldMutate' => [
                'startLine' => 24,
                'endLine' => 35,
                'executable' => 5,
                'executed' => 4,
                'coverage' => 80,
            ],
        ];

        $coverage = $this->parser->parse($this->getXml());

        $this->assertSame($expectedByMethodArray, $coverage[$firstLevelAbsolutePath]['byMethod']);
    }
}