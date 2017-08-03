<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;


use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use PHPUnit\Framework\TestCase;
use Mockery;

class CodeCoverageDataTest extends TestCase
{
    private $coverageDir = __DIR__ . '/../../Files/phpunit/coverage-xml';


    public function test_it_determines_if_method_was_executed_from_coverage_report()
    {
        $codeCoverageData = $this->getCodeCoverageData();

        $filePath = '/tests/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 19), 'Start line'); // signature line
        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 21), 'Body'); // inside body
        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 22), 'End line'); // end line
    }

    public function test_it_determines_line_is_not_covered_by_executed_method()
    {
        $codeCoverageData = $this->getCodeCoverageData();

        $filePath = '/tests/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 1), 'Before');
        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 40), 'After');
    }

    public function test_it_determines_line_is_not_covered_by_not_executed_method()
    {
        $codeCoverageData = $this->getCodeCoverageData();

        $filePath = '/tests/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 4));
    }

    public function test_it_determines_line_is_not_covered_for_unknown_path()
    {
        $codeCoverageData = $this->getCodeCoverageData();

        $filePath = 'unknown/path';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 4));
    }

    private function getParsedCodeCoverageData(): array
    {
        return [
            '/tests/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php' => [
                'byLine' => [
                    26 =>
                        [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    30 =>
                        [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    31 =>
                        [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    34 =>
                        [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                        ],
                ],
                'byMethod' =>
                    [
                        'mutate' => [
                            'startLine' => 19,
                            'endLine' => 22,
                            'executable' => 1,
                            'executed' => 1,
                            'coverage' => 0,
                        ],
                        'shouldMutate' => [
                            'startLine' => 24,
                            'endLine' => 35,
                            'executable' => 5,
                            'executed' => 4,
                            'coverage' => 80,
                        ],
                        'notExecuted' => [
                            'startLine' => 3,
                            'endLine' => 5,
                            'executable' => 5,
                            'executed' => 0,
                            'coverage' => 80,
                        ],
                    ],
            ],
        ];
    }

    private function getCodeCoverageData(): CodeCoverageData
    {
        $coverageXmlParserMock = Mockery::mock(CoverageXmlParser::class);
        $coverageXmlParserMock->shouldReceive('parse')->once()->andReturn($this->getParsedCodeCoverageData());

        return new CodeCoverageData($this->coverageDir, $coverageXmlParserMock);
    }
}
