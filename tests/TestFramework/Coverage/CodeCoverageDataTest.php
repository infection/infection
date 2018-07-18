<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;

use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\TestFrameworkTypes;
use Mockery;

/**
 * @internal
 */
final class CodeCoverageDataTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    private $coverageDir = __DIR__ . '/../../Fixtures/Files/phpunit/coverage-xml';

    public function test_it_determines_if_method_was_executed_from_coverage_report(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 19), 'Start line'); // signature line
        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 21), 'Body'); // inside body
        $this->assertTrue($codeCoverageData->hasExecutedMethodOnLine($filePath, 22), 'End line'); // end line
    }

    public function test_it_determines_line_is_not_covered_by_executed_method(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 1), 'Before');
        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 40), 'After');
    }

    public function test_it_determines_line_is_not_covered_by_not_executed_method(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 4));
    }

    public function test_it_determines_line_is_not_covered_for_unknown_path(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = 'unknown/path';

        $this->assertFalse($codeCoverageData->hasExecutedMethodOnLine($filePath, 4));
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

        $this->assertFalse($codeCoverageData->hasTestsOnLine($filePath, 3));
    }

    public function test_it_determines_file_does_not_have_tests_for_line(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertFalse($codeCoverageData->hasTestsOnLine($filePath, 1));
    }

    public function test_it_determines_file_has_tests_for_line(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();

        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $this->assertTrue($codeCoverageData->hasTestsOnLine($filePath, 30));
    }

    public function test_it_returns_zero_tests_for_not_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $mutation = new Mutation(
            $filePath,
            [],
            Mockery::mock(Mutator::class),
            ['startLine' => 1, 'endLine' => 1],
            'PHPParser\Node\Expr\BinaryOp\Plus',
            false,
            true
        );

        $this->assertCount(0, $codeCoverageData->getAllTestsFor($mutation));
    }

    public function test_it_returns_tests_for_covered_function_body_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $mutation = new Mutation(
            $filePath,
            [],
            Mockery::mock(Mutator::class),
            ['startLine' => 26, 'endLine' => 26],
            'PHPParser\Node\Expr\BinaryOp\Plus',
            false,
            true
        );

        $this->assertCount(2, $codeCoverageData->getAllTestsFor($mutation));
    }

    public function test_it_returns_zero_tests_for_not_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $mutation = new Mutation(
            $filePath,
            [],
            Mockery::mock(Mutator::class),
            ['startLine' => 1, 'endLine' => 1],
            'PHPParser\Node\Stmt\ClassMethod',
            true,
            true
        );

        $this->assertCount(0, $codeCoverageData->getAllTestsFor($mutation));
    }

    public function test_it_returns_tests_for_covered_function_signature_mutator(): void
    {
        $codeCoverageData = $this->getCodeCoverageData();
        $filePath = '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php';

        $mutation = new Mutation(
            $filePath,
            [],
            Mockery::mock(Mutator::class),
            ['startLine' => 24, 'endLine' => 24],
            'PHPParser\Node\Stmt\ClassMethod',
            true,
            true
        );

        $this->assertCount(6, $codeCoverageData->getAllTestsFor($mutation));
    }

    public function test_it_throws_an_exception_when_no_coverage_found(): void
    {
        $coverageXmlParserMock = Mockery::mock(CoverageXmlParser::class);

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
            '/tests/Fixtures/Files/phpunit/coverage-xml/FirstLevel/firstLevel.php' => [
                'byLine' => [
                    26 => [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    30 => [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    31 => [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_not_mutate_plus_with_arrays'],
                        ],
                    34 => [
                            ['testMethod' => 'Infection\\Tests\\Mutator\\Arithmetic\\PlusTest::test_it_should_mutate_plus_expression'],
                        ],
                ],
                'byMethod' => [
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
                            'coverage' => 0, // not executed method can't be covered
                        ],
                    ],
            ],
        ];
    }

    private function getCodeCoverageData(): CodeCoverageData
    {
        $coverageXmlParserMock = Mockery::mock(CoverageXmlParser::class);
        $coverageXmlParserMock->shouldReceive('parse')->once()->andReturn($this->getParsedCodeCoverageData());

        return new CodeCoverageData($this->coverageDir, $coverageXmlParserMock, TestFrameworkTypes::PHPUNIT);
    }
}
