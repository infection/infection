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

namespace Infection\Tests\Logger\Html;

use function array_map;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Framework\Str;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Metrics\Collector;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Removal\ArrayItemRemoval;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Testing\MutatorName;
use JsonSchema\Validator;
use function Later\now;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\base64_decode;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\realpath;
use function sprintf;

#[Group('integration')]
#[CoversClass(StrykerHtmlReportBuilder::class)]
final class StrykerHtmlReportBuilderTest extends TestCase
{
    private const SCHEMA_FILE = 'file://' . __DIR__ . '/../../../../resources/mutation-testing-report-schema.json';

    /**
     * @param array<string, array<string, mixed>|string> $expectedReport
     */
    #[DataProvider('metricsProvider')]
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        array $expectedReport,
    ): void {
        $report = (new StrykerHtmlReportBuilder($metricsCalculator, $resultsCollector))->build();

        $this->assertSame($expectedReport, json_decode(json_encode($report), true));
        $this->assertJsonDocumentMatchesSchema($report);
    }

    public static function metricsProvider(): iterable
    {
        yield 'no mutations' => [
            new MetricsCalculator(2),
            new ResultsCollector(),
            [
                'schemaVersion' => '1',
                'thresholds' => [
                    'high' => 90,
                    'low' => 50,
                ],
                'files' => [],
                'testFiles' => [],
                'framework' => [
                    'name' => 'Infection',
                    'branding' => [
                        'homepageUrl' => 'https://infection.github.io/',
                        'imageUrl' => 'https://infection.github.io/images/logo.png',
                    ],
                ],
            ],
        ];

        $realPathForHtmlReport = realpath(__DIR__ . '/../../Fixtures/ForHtmlReport.php');
        $realPathForHtmlReport2 = realpath(__DIR__ . '/../../Fixtures/ForHtmlReport2.php');

        yield 'different mutations' => [
            self::createFullHtmlReportMetricsCalculator(),
            self::createFullHtmlReportResultsCollector(),
            [
                'schemaVersion' => '1',
                'thresholds' => [
                    'high' => 90,
                    'low' => 50,
                ],
                'files' => [
                    'ForHtmlReport.php' => [
                        'language' => 'php',
                        'source' => file_get_contents($realPathForHtmlReport),
                        'mutants' => [
                            [
                                'id' => '32f68ca331c9262cc97322271d88d06d',
                                'mutatorName' => 'PublicVisibility',
                                'replacement' => 'protected function add(int $a, int $b) : int',
                                'description' => 'Replaces the `public` method visibility keyword with `protected`.',
                                'location' => ['start' => ['line' => 13, 'column' => 5], 'end' => ['line' => 13, 'column' => 45]],
                                'status' => 'Killed',
                                'statusReason' => 'PHPUnit output. Tests: 1, Assertions: 3',
                                'coveredBy' => ['06a6c58caae5aa33e9b787f064618f5e'],
                                'killedBy' => [],
                                'testsCompleted' => 1,
                            ],
                            [
                                'id' => 'fd66aff56e903645c21271264b062b4f',
                                'mutatorName' => 'MethodCallRemoval',
                                'replacement' => '',
                                'description' => 'Removes the method call.',
                                'location' => ['start' => ['line' => 15, 'column' => 9], 'end' => ['line' => 15, 'column' => 27]],
                                'status' => 'Survived',
                                'statusReason' => 'PHPUnit output. Tests: 1, Assertions: 3. Failure: 1) TestClass::test_method1 Failed',
                                'coveredBy' => ['06a6c58caae5aa33e9b787f064618f5e'],
                                'killedBy' => ['06a6c58caae5aa33e9b787f064618f5e'],
                                'testsCompleted' => 1,
                            ],
                            [
                                'id' => '746519c01522ddc7da799a9b7927e4c2',
                                'mutatorName' => 'MethodCallRemoval',
                                'replacement' => '',
                                'description' => 'Removes the method call.',
                                'location' => ['start' => ['line' => 17, 'column' => 9], 'end' => ['line' => 19, 'column' => 11]],
                                'status' => 'Survived',
                                'statusReason' => 'PHPUnit output. Tests: 1, Assertions: 3. Failure: 1) TestClass::test_method1 with data set #1',
                                'coveredBy' => ['2b67abde50b026f4057311ea32409632'],
                                'killedBy' => ['2b67abde50b026f4057311ea32409632'],
                                'testsCompleted' => 1,
                            ],
                            [
                                'id' => '633b144fb6d55bbc60430df68a952388',
                                'mutatorName' => 'ArrayItemRemoval',
                                'replacement' => '$this->innerArray(array_keys([\'b\' => \'2\']));',
                                'description' => "Removes an element of an array literal. For example:\n\n```php\n\$x = [0, 1, 2];\n```\n\nWill be mutated to:\n\n```php\n\$x = [1, 2];\n```\n\nAnd:\n\n```php\n\$x = [0, 2];\n```\n\nAnd:\n\n```php\n\$x = [0, 1];\n```\n\nWhich elements it removes or how many elements it will attempt to remove will depend on its\nconfiguration.\n",
                                'location' => ['start' => ['line' => 28, 'column' => 9], 'end' => ['line' => 28, 'column' => 65]],
                                'status' => 'Survived',
                                'statusReason' => 'PHPUnit output. Tests: 3, Assertions: 3',
                                'coveredBy' => ['06a6c58caae5aa33e9b787f064618f5e', '949bee6dd4ac608462995babbe81ee12', '2733f8c97b5ba92b1aacb77d46837b0e'],
                                'killedBy' => [],
                                'testsCompleted' => 3,
                            ],
                        ],
                    ],
                    'ForHtmlReport2.php' => [
                        'language' => 'php',
                        'source' => file_get_contents($realPathForHtmlReport2),
                        'mutants' => [
                            [
                                'id' => '12f68ca331c9262cc97322271d88d06d',
                                'mutatorName' => 'PublicVisibility',
                                'replacement' => 'protected function add(int $a, int $b) : int',
                                'description' => 'Replaces the `public` method visibility keyword with `protected`.',
                                'location' => ['start' => ['line' => 13, 'column' => 5], 'end' => ['line' => 13, 'column' => 6]],
                                'status' => 'Killed',
                                'statusReason' => 'Output without ability to detect the number of executed tests',
                                'coveredBy' => ['06a6c58caae5aa33e9b787f064618f5e'],
                                'killedBy' => [],
                                'testsCompleted' => 0,
                            ],
                            [
                                'id' => '22f68ca331c9262cc97322271d88d06d',
                                'mutatorName' => 'PublicVisibility',
                                'replacement' => 'protected function add(int $a, int $b) : int',
                                'description' => 'Replaces the `public` method visibility keyword with `protected`.',
                                'location' => ['start' => ['line' => 13, 'column' => 5], 'end' => ['line' => 13, 'column' => 6]],
                                'status' => 'Killed',
                                'statusReason' => 'Output without ability to detect the number of executed testsi?',
                                'coveredBy' => ['06a6c58caae5aa33e9b787f064618f5e'],
                                'killedBy' => [],
                                'testsCompleted' => 0,
                            ],
                        ],
                    ],
                ],
                'testFiles' => [
                    '/infection/path/to/TestClass.php' => [
                        'tests' => [
                            [
                                'id' => '06a6c58caae5aa33e9b787f064618f5e',
                                'name' => 'TestClass::test_method1',
                            ],
                            [
                                'id' => '2b67abde50b026f4057311ea32409632',
                                'name' => 'TestClass::test_method1 with data set #1',
                            ],
                        ],
                    ],
                    '/infection/path/to/TestClass2.php' => [
                        'tests' => [
                            [
                                'id' => '949bee6dd4ac608462995babbe81ee12',
                                'name' => 'TestClass2::test_method2',
                            ],
                            [
                                'id' => '2733f8c97b5ba92b1aacb77d46837b0e',
                                'name' => 'TestClass2::test_method3',
                            ],
                        ],
                    ],
                ],
                'framework' => [
                    'name' => 'Infection',
                    'branding' => [
                        'homepageUrl' => 'https://infection.github.io/',
                        'imageUrl' => 'https://infection.github.io/images/logo.png',
                    ],
                ],
            ],
        ];
    }

    private static function createFullHtmlReportMetricsCalculator(): MetricsCalculator
    {
        $collector = new MetricsCalculator(2);

        self::initHtmlReportCollector($collector);

        return $collector;
    }

    private static function createFullHtmlReportResultsCollector(): ResultsCollector
    {
        $collector = new ResultsCollector();

        self::initHtmlReportCollector($collector);

        return $collector;
    }

    private function assertJsonDocumentMatchesSchema(mixed $report): void
    {
        $resultReport = json_decode(json_encode($report));

        $validator = new Validator();

        $validator->validate($resultReport, (object) ['$ref' => self::SCHEMA_FILE]);

        $normalizedErrors = array_map(
            static fn (array $error): string => sprintf('[%s] %s%s', $error['property'], $error['message'], PHP_EOL),
            $validator->getErrors(),
        );

        $this->assertTrue(
            $validator->isValid(),
            sprintf(
                'Expected the given JSON to be valid but is violating the following rules of'
                . ' the schema: %s- %s',
                PHP_EOL,
                implode('- ', $normalizedErrors),
            ),
        );
    }

    private static function initHtmlReportCollector(Collector $collector): void
    {
        $collector->collect(
            // this tests diffs on the method signature line
            self::createMutantExecutionResult(
                DetectionStatus::KILLED_BY_TESTS,
                <<<'DIFF'
                    @@ @@
                     use function array_fill_keys;
                     final class ForHtmlReport
                     {
                    -    public function add(int $a, int $b) : int
                    +    protected function add(int $a, int $b) : int
                         {
                             $this->inner('3');
                             $this->inner('3');
                    DIFF,
                '32f68ca331c9262cc97322271d88d06d',
                IgnoreMutator::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport.php'),
                13,
                35,
                124,
                547,
                [
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                    // check that duplicate values are moved in the report
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                ],
                'PHPUnit output. Tests: 1, Assertions: 3',
                'PublicVisibility',
            ),
            // this tests diff on the one-line method call removal
            self::createMutantExecutionResult(
                DetectionStatus::ESCAPED,
                <<<'DIFF'
                    @@ @@
                     {
                         public function add(int $a, int $b) : int
                         {
                    -        $this->inner('3');
                    +
                             $this->inner('3');
                             switch (true) {
                                 case 1 !== 1:
                    DIFF,
                'fd66aff56e903645c21271264b062b4f',
                MethodCallRemoval::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport.php'),
                15,
                15,
                179,
                196,
                [
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                ],
                'PHPUnit output. Tests: 1, Assertions: 3. Failure: 1) TestClass::test_method1 Failed',
            ),
            // this tests diff on the multi-line (in original source code) method call removal
            self::createMutantExecutionResult(
                DetectionStatus::ESCAPED,
                <<<'DIFF'
                    @@ @@
                         public function add(int $a, int $b) : int
                         {
                             $this->inner('3');
                    -        $this->inner('3');
                    +
                             switch (true) {
                                 case 0 !== 1:
                                     break;
                    DIFF,
                '746519c01522ddc7da799a9b7927e4c2',
                MethodCallRemoval::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport.php'),
                17,
                19,
                207,
                246,
                [
                    new TestLocation('TestClass::test_method1 with data set #1', '/infection/path/to/TestClass.php', 0.123),
                ],
                'PHPUnit output. Tests: 1, Assertions: 3. Failure: 1) TestClass::test_method1 with data set #1',
            ),
            // this tests diff on the one-line diff with array item removal
            self::createMutantExecutionResult(
                DetectionStatus::ESCAPED,
                <<<'DIFF'
                    @@ @@
                                 default:
                                     break;
                             }
                    -        $this->innerArray(array_keys(['a' => '1', 'b' => '2']));
                    +        $this->innerArray(array_keys(['b' => '2']));
                             if ($this instanceof ForHtmlReport) {
                                 // ...
                             }
                    DIFF,
                '633b144fb6d55bbc60430df68a952388',
                ArrayItemRemoval::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport.php'),
                28,
                28,
                414,
                437,
                [
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                    new TestLocation('TestClass2::test_method2', '/infection/path/to/TestClass2.php', 0.456),
                    new TestLocation('TestClass2::test_method3', '/infection/path/to/TestClass2.php', 0.789),
                ],
                'PHPUnit output. Tests: 3, Assertions: 3',
            ),
            // add one test for the second file
            self::createMutantExecutionResult(
                DetectionStatus::KILLED_BY_TESTS,
                <<<'DIFF'
                    @@ @@
                     use function array_fill_keys;
                     final class ForHtmlReport2
                     {
                    -    public function add(int $a, int $b) : int
                    +    protected function add(int $a, int $b) : int
                         {
                             return 0;
                    DIFF,
                '12f68ca331c9262cc97322271d88d06d',
                PublicVisibility::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport2.php'),
                13,
                35,
                124,
                547,
                [
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                ],
                'Output without ability to detect the number of executed tests',
            ),
            // with non UTF-8 character
            self::createMutantExecutionResult(
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                <<<'DIFF'
                    @@ @@
                     use function array_fill_keys;
                     final class ForHtmlReport2
                     {
                    -    public function add(int $a, int $b) : int
                    +    protected function add(int $a, int $b) : int
                         {
                             return 0;
                    DIFF,
                '22f68ca331c9262cc97322271d88d06d',
                PublicVisibility::class,
                realpath(__DIR__ . '/../../Fixtures/ForHtmlReport2.php'),
                13,
                35,
                124,
                547,
                [
                    new TestLocation('TestClass::test_method1', '/infection/path/to/TestClass.php', 0.123),
                ],
                'Output without ability to detect the number of executed tests' . base64_decode('abc', true), // produces non UTF-8 character
            ),
        );
    }

    /**
     * @param array<int, TestLocation> $testLocations
     */
    private static function createMutantExecutionResult(
        DetectionStatus $detectionStatus,
        string $diff,
        string $mutantHash,
        string $mutatorClassName,
        string $originalFileRealPath,
        int $originalStartingLine,
        int $originalEndingLine,
        int $originalStartFilePosition,
        int $originalEndFilePosition,
        array $testLocations,
        ?string $processOutput = 'PHPUnit output. Tests: 1, Assertions: 3',
        ?string $mutatorName = null,
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            $processOutput,
            $detectionStatus,
            now(Str::rTrimLines($diff)),
            $mutantHash,
            $mutatorClassName,
            $mutatorName ?? MutatorName::getName($mutatorClassName),
            $originalFileRealPath,
            $originalStartingLine,
            $originalEndingLine,
            $originalStartFilePosition,
            $originalEndFilePosition,
            now('<?php $a = 1;'),
            now('<?php $a = 2;'),
            $testLocations,
            0.0,
        );
    }
}
