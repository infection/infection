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

namespace Infection\Logger\Html;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_slice;
use function array_unique;
use ArrayObject;
use function count;
use function current;
use function explode;
use function file_get_contents;
use function implode;
use function in_array;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Removal\MethodCallRemoval;
use function ltrim;
use function md5;
use const PHP_EOL;
use function preg_match;
use function strlen;
use function strpos;
use function substr;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

final class StrykerHtmlReportBuilder
{
    private const DETECTION_STATUS_MAP = [
        DetectionStatus::KILLED => 'Killed',
        DetectionStatus::ESCAPED => 'Survived',
        DetectionStatus::ERROR => 'RuntimeError',
        DetectionStatus::TIMED_OUT => 'Timeout',
        DetectionStatus::NOT_COVERED => 'NoCoverage',
        DetectionStatus::SYNTAX_ERROR => 'CompileError',
        DetectionStatus::IGNORED => 'Ignored',
    ];

    private const PLUS_LENGTH = 1;

    private MetricsCalculator $metricsCalculator;
    private ResultsCollector $resultsCollector;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->resultsCollector = $resultsCollector;
    }

    public function build(): array
    {
        return [
            'schemaVersion' => '1',
            'thresholds' => [
                'high' => 90,
                'low' => 50,
            ],
            'files' => $this->getFiles(),
            'testFiles' => $this->getTestFiles(),
            // 'performance' => [], todo
            'framework' => [
                'name' => 'Infection',
                'branding' => [
                    'homepageUrl' => 'https://infection.github.io/',
                    'imageUrl' => 'https://infection.github.io/images/logo.png',
                ],
            ],
        ];
    }

    private function getTestFiles(): ArrayObject
    {
        $testFiles = [];
        $allTests = [];

        foreach ($this->resultsCollector->getAllExecutionResults() as $result) {
            $allTests[] = $result->getTests();
        }

        $allTests = array_merge(...$allTests);

        $usedTests = [];

        $uniqueTests = array_reduce($allTests, static function (array $carry, TestLocation $testLocation) use (&$usedTests) {
            $key = $testLocation->getFilePath() . $testLocation->getMethod();

            if (!array_key_exists($key, $usedTests)) {
                $carry[] = $testLocation;

                $usedTests[$key] = true;
            }

            return $carry;
        }, []);

        foreach ($uniqueTests as $testLocation) {
            if (!array_key_exists($testLocation->getFilePath(), $testFiles)) {
                $testFiles[$testLocation->getFilePath()] = [
                    'tests' => [$this->buildTest($testLocation)],
                ];
            } else {
                $testFiles[$testLocation->getFilePath()]['tests'][] = $this->buildTest($testLocation);
            }
        }

        return new ArrayObject($testFiles);
    }

    private function getFiles(): ArrayObject
    {
        $files = new ArrayObject();

        if ($this->metricsCalculator->getTotalMutantsCount() !== 0) {
            $resultsByPath = $this->retrieveResultsByPath();
            $basePath = Path::getLongestCommonBasePath(array_keys($resultsByPath));

            $files = $this->retrieveFiles($resultsByPath, $basePath);
        }

        return $files;
    }

    /**
     * @param array<string, MutantExecutionResult[]> $resultsByPath
     */
    private function retrieveFiles(array $resultsByPath, string $basePath): ArrayObject
    {
        $files = new ArrayObject();

        foreach ($resultsByPath as $path => $results) {
            $relativePath = $path === $basePath ? $path : Path::makeRelative($path, $basePath);

            $result = current($results);
            Assert::isInstanceOf($result, MutantExecutionResult::class);

            $originalCode = file_get_contents($path);

            Assert::string($originalCode);

            $files[$relativePath] = [
                'language' => 'php',
                'source' => file_get_contents($path),
                'mutants' => $this->retrieveMutants($results, $originalCode),
            ];
        }

        return $files;
    }

    /**
     * @return array<string, MutantExecutionResult[]>
     */
    private function retrieveResultsByPath(): array
    {
        $results = [];

        foreach ($this->resultsCollector->getAllExecutionResults() as $result) {
            $results[$result->getOriginalFilePath()][] = $result;
        }

        return $results;
//        return count($results) > 1 ? array_slice($results, 0, 1, true) : $results;
    }

    /**
     * @param MutantExecutionResult[] $results
     */
    private function retrieveMutants(array $results, string $originalCode): array
    {
        return array_map(
            function (MutantExecutionResult $result) use ($originalCode): array {
                $fileAsArrayOfLines = explode(PHP_EOL, $originalCode);
                $replacement = $this->retrieveReplacementFromDiff($result->getMutantDiff());

                $originalCodeLine = $fileAsArrayOfLines[$result->getOriginalStartingLine() - 1];
                $originalCodeLineLength = strlen($originalCodeLine) + 1;

                $startingColumn = $originalCodeLineLength - strlen(ltrim($originalCodeLine));
                $endingColumn = $originalCodeLineLength;

                $methodSignatureMutators = [
                    MutatorFactory::getMutatorNameForClassName(PublicVisibility::class),
                    MutatorFactory::getMutatorNameForClassName(ProtectedVisibility::class),
                ];

                $endingLine = in_array($result->getMutatorName(), $methodSignatureMutators, true)
                    ? $result->getOriginalStartingLine()
                    : $result->getOriginalEndingLine();

                // needed when remove method is on multiple lines
                if ($result->getMutatorName() === MutatorFactory::getMutatorNameForClassName(MethodCallRemoval::class)) {
                    $endingColumn = $result->getOriginalEndingColumn($originalCode) + 1;
                }

//                var_dump($result->getMutatorName());
//                var_dump($result->getMutantDiff());
//                var_dump($result->getOriginalStartingLine());
//                var_dump($result->getOriginalEndingLine());
//                var_dump($result->originalStartFilePosition);
//                var_dump($result->originalEndFilePosition);
//                var_dump($endingLine);
//                var_dump($startingColumn);
//                var_dump($endingColumn);
//                var_dump($result->getOriginalStartingColumn($originalCode));
//                var_dump($result->getOriginalEndingColumn($originalCode));

                return [
                    'id' => $result->getMutantHash(),
                    'mutatorName' => $result->getMutatorName(),
                    'replacement' => ltrim($replacement),
                    'description' => $this->getMutatorDescription($result->getMutatorName()),
                    'location' => [
                        'start' => ['line' => $result->getOriginalStartingLine(), 'column' => $startingColumn],
                        'end' => ['line' => $endingLine, 'column' => $endingColumn],
                    ],
                    'status' => self::DETECTION_STATUS_MAP[$result->getDetectionStatus()],
                    'statusReason' => $result->getProcessOutput(),
                    'coveredBy' => array_unique(array_map( // todo unique? ask @sanmai
                        fn (TestLocation $testLocation): string => $this->buildTestMethodId($testLocation->getMethod()),
                        $result->getTests()
                    )),
                    'killedBy' => $this->getKilledBy($result->getProcessOutput()),
                    'testsCompleted' => $this->getTestsCompleted($result->getProcessOutput()),
                ];
            },
            $results
        );
    }

    private function retrieveReplacementFromDiff(string $diff): string
    {
        $lines = explode(PHP_EOL, $diff);

        $lines = array_map(
            static function (string $line): string {
                return isset($line[0]) ? substr($line, self::PLUS_LENGTH) : $line;
            },
            array_filter(
            /*
            --- Original
            +++ New
            @@ @@
             */
                array_slice($lines, 3),
                static function (string $line): bool {
                    return isset($line[0]) && strpos($line, '+') === 0;
                }
            )
        );

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array<int, string>
     */
    private function getKilledBy(string $processOutput): array
    {
        $matches = [];

        if (preg_match('/(?<name>\S+::\S+)(?:(?<dataname> with data set (?:#\d+|"[^"]+"))\s\()?/', $processOutput, $matches)) {
            return [$this->buildTestMethodId($matches['name'] . ($matches['dataname'] ?? ''))];
        }

        return [];
    }

    private function getTestsCompleted(string $processOutput): int
    {
        $matches = [];

        if (preg_match('/Tests:\s(\d+),\sAssertions/', $processOutput, $matches)) {
            return (int) ($matches[1] ?? 0);
        }

        return 0;
    }

    private function getMutatorDescription(string $mutatorName): string
    {
        Assert::keyExists(ProfileList::ALL_MUTATORS, $mutatorName);

        /** @var Mutator $mutatorClass */
        $mutatorClass = ProfileList::ALL_MUTATORS[$mutatorName];

        $definition = $mutatorClass::getDefinition();

        Assert::notNull($definition);

        return $definition->getDescription();
    }

    private function buildTest($testLocation): array
    {
        return [
            'id' => $this->buildTestMethodId($testLocation->getMethod()),
            'name' => $testLocation->getMethod(),
        ];
    }

    private function buildTestMethodId(string $testMethod): string
    {
        return md5($testMethod);
    }
}
