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

namespace Infection\Tests\TestFramework\Coverage\JUnit\JUnitReport;

use Infection\TestFramework\Coverage\JUnit\JUnitReport;
use Infection\TestFramework\Coverage\Throwable\TestNotFound;
use Infection\Tests\Mutator\FunctionSignature\ProtectedVisibilityTest;
use Infection\Tests\Mutator\Unwrap\UnwrapArrayIntersectUassocTest;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use InvalidArgumentException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Throwable;

/**
 * @phpstan-import-type TestInfo from JUnitReport
 */
#[CoversClass(JUnitReport::class)]
final class JUnitReportTest extends TestCase
{
    use ExpectsThrowables;

    private const FIXTURE_DIR = __DIR__ . '/Fixtures';

    public function test_it_throws_an_exception_if_the_report_file_does_not_exist(): void
    {
        $report = new JUnitReport('/path/to/unknown.xml');

        $this->expectExceptionObject(
            new InvalidArgumentException('The path "/path/to/unknown.xml" is not a file.'),
        );

        // We need to request a test info for to initiate the parsing/loading of the file.
        $report->getTestInfo('ThisValueDoesNotMatterForThisTest');
    }

    public function test_it_throws_an_exception_if_the_report_file_is_invalid_xml(): void
    {
        $report = new JUnitReport(__FILE__);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                sprintf(
                    'The file "%s" does not contain valid XML.',
                    __FILE__,
                ),
            ),
        );

        $report->getTestInfo('App\UnknownTest');
    }

    /**
     * @param non-empty-array<class-string, TestInfo|class-string<Throwable>> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_can_get_the_test_info_for_a_given_test_id(
        string $xmlPathname,
        array $expected,
    ): void {
        $report = new JUnitReport(
            Path::canonicalize($xmlPathname),
        );

        $actual = [];

        foreach ($expected as $testId => $expectedInfo) {
            if (is_string($expectedInfo)) {
                $actualException = $this->expectToThrow(
                    static fn () => $report->getTestInfo($testId),
                );
                $this->assertInstanceOf($expectedInfo, $actualException);
                unset($expected[$testId]);
            } else {
                $actual[$testId] = $report->getTestInfo($testId);
            }
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param non-empty-array<class-string, TestInfo|class-string<Throwable>> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_is_idempotent(
        string $xmlPathname,
        array $expected,
    ): void {
        $report = new JUnitReport(
            Path::canonicalize($xmlPathname),
        );

        $resultOfTheFirstTraverse = [];
        $resultOfTheFirstTraverseSecondCall = [];
        $resultOfTheSecondTraverse = [];

        foreach ($expected as $testId => $expectedInfo) {
            if (is_string($expectedInfo)) {
                $resultOfTheFirstTraverse[$testId] = $this->expectToThrow(
                    static fn () => $report->getTestInfo($testId),
                );
                $resultOfTheFirstTraverseSecondCall[$testId] = $this->expectToThrow(
                    static fn () => $report->getTestInfo($testId),
                );
            } else {
                $resultOfTheFirstTraverse[$testId] = $report->getTestInfo($testId);
                $resultOfTheFirstTraverseSecondCall[$testId] = $report->getTestInfo($testId);
            }
        }

        foreach ($expected as $testId => $expectedInfo) {
            if (is_string($expectedInfo)) {
                $resultOfTheSecondTraverse[$testId] = $this->expectToThrow(
                    static fn () => $report->getTestInfo($testId),
                );
            } else {
                $resultOfTheSecondTraverse[$testId] = $report->getTestInfo($testId);
            }
        }

        $this->assertEquals($resultOfTheFirstTraverse, $resultOfTheFirstTraverseSecondCall);
        $this->assertEquals($resultOfTheFirstTraverse, $resultOfTheSecondTraverse);
    }

    public static function infoProvider(): iterable
    {
        yield 'JUnit file generated by PHPUnit' => [
            self::FIXTURE_DIR . '/phpunit-junit.xml',
            [
                // TODO: Would be nice to support it
                'Infection\Tests\Mutator\Unwrap\UnwrapArrayIntersectUassocTest::test_it_can_mutate with data set &quot;It does not mutate when a variable function name is used&quot;' => TestNotFound::class,
                // TODO: Would be nice to support it
                'Infection\Tests\Mutator\Unwrap\UnwrapArrayIntersectUassocTest::test_it_can_mutate with data' => TestNotFound::class,
                UnwrapArrayIntersectUassocTest::class => self::createTestInfo(
                    '/path/to/project/tests/phpunit/Mutator/Unwrap/UnwrapArrayIntersectUassocTest.php',
                    0.912992,
                ),
                ProtectedVisibilityTest::class => self::createTestInfo(
                    '/path/to/project/tests/phpunit/Mutator/FunctionSignature/ProtectedVisibilityTest.php',
                    0.053797,
                ),
            ],
        ];

        // https://github.com/infection/infection/pull/800
        yield 'JUnit file generated by Codeception' => [
            self::FIXTURE_DIR . '/codeception-junit.xml',
            [
                'App\Tests\unit\SourceClassTest' => self::createTestInfo(
                    '/codeception/tests/unit/SourceClassTest.php',
                    0.006096,
                ),
            ],
        ];

        // https://codeception.com/docs/BDD
        // https://github.com/infection/infection/pull/1034
        yield 'JUnit file generated by CodeceptionBDD' => [
            self::FIXTURE_DIR . '/codeception-bdd-junit.xml',
            [
                'FeatureA:Scenario A1' => self::createTestInfo(
                    '/codeception/tests/bdd/FeatureA.feature',
                    0.039365,
                ),
            ],
        ];

        // https://github.com/infection/infection/pull/1503
        yield 'JUnit file generated by CodeceptionCest' => [
            self::FIXTURE_DIR . '/codeception-cest-junit.xml',
            [
                'app\controllers\ExampleCest:FeatureA' => self::createTestInfo(
                    '/app/controllers/ExampleCest.php',
                    1.0E-6,
                ),
            ],
        ];
    }

    /**
     * @return TestInfo
     */
    private static function createTestInfo(
        string $location,
        float $executionTime,
    ): array {
        return [
            'location' => $location,
            'executionTime' => $executionTime,
        ];
    }
}
