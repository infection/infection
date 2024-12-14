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
use function current;
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
use Infection\Mutator\MutatorResolver;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Str;
use function ltrim;
use function md5;
use const PHP_EOL;
use PhpParser\NodeAbstract;
use function Safe\file_get_contents;
use function Safe\preg_match;
use function Safe\preg_split;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class StrykerHtmlReportBuilder
{
    private const DETECTION_STATUS_MAP = [
        DetectionStatus::KILLED => 'Killed',
        DetectionStatus::ESCAPED => 'Survived',
        DetectionStatus::ERROR => 'RuntimeError',
        DetectionStatus::TIMED_OUT => 'Timeout',
        DetectionStatus::NOT_COVERED => 'NoCoverage',
        DetectionStatus::SYNTAX_ERROR => 'CompileError',
        DetectionStatus::IGNORED => 'Ignored',
        DetectionStatus::SKIPPED => 'Ignored',
    ];

    private const PLUS_LENGTH = 1;
    private const DIFF_HEADERS_LINES_COUNT = 1;

    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector)
    {
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
            $key = $testLocation->getMethod();

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

            Assert::minCount($resultsByPath, 1, 'There must be at least one result to build HTML report.');

            $basePath = Path::getLongestCommonBasePath(...array_keys($resultsByPath));

            Assert::string($basePath, '$basePath must be a string');

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
    }

    /**
     * @param MutantExecutionResult[] $results
     */
    private function retrieveMutants(array $results, string $originalCode): array
    {
        return array_map(
            function (MutantExecutionResult $result) use ($originalCode): array {
                $fileAsArrayOfLines = preg_split('/\n|\r\n?/', $originalCode);
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

                // needed when removed method is on multiple lines
                if ($result->getMutatorName() === MutatorFactory::getMutatorNameForClassName(MethodCallRemoval::class)) {
                    $endingColumn = $result->getOriginalEndingColumn($originalCode) + 1;
                }

                return [
                    'id' => $result->getMutantHash(),
                    'mutatorName' => $result->getMutatorName(),
                    'replacement' => Str::convertToUtf8(Str::trimLineReturns(ltrim($replacement))),
                    'description' => $this->getMutatorDescription($result->getMutatorName(), $result->getMutatorClass()),
                    'location' => [
                        'start' => ['line' => $result->getOriginalStartingLine(), 'column' => $startingColumn],
                        'end' => ['line' => $endingLine, 'column' => $endingColumn],
                    ],
                    'status' => self::DETECTION_STATUS_MAP[$result->getDetectionStatus()],
                    'statusReason' => Str::convertToUtf8(Str::trimLineReturns($result->getProcessOutput())),
                    'coveredBy' => array_unique(array_map(
                        fn (TestLocation $testLocation): string => $this->buildTestMethodId($testLocation->getMethod()),
                        $result->getTests(),
                    )),
                    'killedBy' => $this->getKilledBy($result->getProcessOutput()),
                    'testsCompleted' => $this->getTestsCompleted($result->getProcessOutput()),
                ];
            },
            $results,
        );
    }

    private function retrieveReplacementFromDiff(string $diff): string
    {
        $lines = preg_split('/\n|\r\n?/', $diff);

        $lines = array_map(
            static fn (string $line): string => isset($line[0]) ? substr($line, self::PLUS_LENGTH) : $line,
            array_filter(
                /*
                @@ @@
                 */
                array_slice($lines, self::DIFF_HEADERS_LINES_COUNT),
                static fn (string $line): bool => str_starts_with($line, '+'),
            ),
        );

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array<int, string>
     */
    private function getKilledBy(string $processOutput): array
    {
        $matches = [];

        if (preg_match('/(?<name>\S+::\S+)(?<dataname> with data set (?:#\d+|"[^"]+"))?/', $processOutput, $matches) === 1) {
            return [$this->buildTestMethodId($matches['name'] . ($matches['dataname'] ?? ''))];
        }

        return [];
    }

    private function getTestsCompleted(string $processOutput): int
    {
        $matches = [];

        if (preg_match('/Tests:\s(\d+),\sAssertions/', $processOutput, $matches) === 1) {
            Assert::keyExists($matches, 1);

            return (int) $matches[1];
        }

        return 0;
    }

    private function getMutatorDescription(string $mutatorName, string $mutatorClass): string
    {
        Assert::true(MutatorResolver::isValidMutator($mutatorClass), sprintf('Unknown mutator "%s"', $mutatorClass));

        /** @var class-string<Mutator<NodeAbstract>> $mutatorClass */
        $mutatorClass = ProfileList::ALL_MUTATORS[$mutatorName] ?? $mutatorClass;

        $definition = $mutatorClass::getDefinition();

        Assert::notNull($definition);

        return $definition->getDescription();
    }

    /**
     * @return array{id: string, name: string}
     */
    private function buildTest(TestLocation $testLocation): array
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
