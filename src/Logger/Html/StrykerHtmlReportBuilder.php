<?php

declare(strict_types=1);


namespace Infection\Logger\Html;


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
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_slice;
use function array_unique;
use function current;
use function explode;
use function file_get_contents;
use function implode;
use function in_array;
use function json_encode;
use function ltrim;
use function md5;
use function preg_match;
use function strlen;
use function strpos;
use function substr;

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
    )
    {
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
//            'testFiles' => $this->getTestFiles(),
            // 'performance' => [], todo
            'framework' => [
                'name' => 'Infection',
                'branding' => [
                    'homepageUrl' => 'https://infection.github.io/',
                    'imageUrl' => 'https://infection.github.io/images/logo.png'
                ]
            ]
        ];
    }

    private function getTestFiles(): \ArrayObject
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
                $testFiles[$testLocation->getFilePath()]= [
                    'tests' => [
                        [
                            'id' => md5($testLocation->getMethod()),
                            'name' => $testLocation->getMethod()
                        ]
                    ]
                ];
            } else {
                $testFiles[$testLocation->getFilePath()]['tests'][] = [
                    'id' => md5($testLocation->getMethod()),
                    'name' => $testLocation->getMethod()
                ];
            }
        }

        return new \ArrayObject($testFiles);
    }

    private function getFiles(): \ArrayObject
    {
        $files = new \ArrayObject();

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
    private function retrieveFiles(array $resultsByPath, string $basePath): \ArrayObject
    {
        $files = new \ArrayObject();

        foreach ($resultsByPath as $path => $results) {
            $relativePath = $path === $basePath ? $path : Path::makeRelative($path, $basePath);

            $result = current($results);
            Assert::isInstanceOf($result, MutantExecutionResult::class);

            $originalCode = file_get_contents($path);

            Assert::string($originalCode);

            $files[$relativePath] = [
                'language' => 'php',
                'source' => file_get_contents($path),
                'mutants' => $this->retrieveMutants($results, $originalCode)
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
//                var_dump($replacement);
//                var_dump($result->getOriginalStartingLine());
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
                        'start' => [
                            'line' => $result->getOriginalStartingLine(),
                            'column' => $startingColumn,
                        ],
                        'end' => [
                            'line' => $endingLine,
                            'column' => $endingColumn,
                        ],
                    ],
                    'status' => self::DETECTION_STATUS_MAP[$result->getDetectionStatus()],
                    'statusReason' => $result->getProcessOutput(),
                    'coveredBy' => array_unique(array_map( // todo unique? ask @sanmai
                        static fn (TestLocation $testLocation): string => md5($testLocation->getMethod()),
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
            /**
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
            return [md5($matches['name'] . ($matches['dataname'] ?? ''))];
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
}
