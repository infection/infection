<?php

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use function array_filter;
use function array_keys;
use function array_map;
use function array_slice;
use function current;
use function explode;
use function implode;
use function iterator_to_array;
use function ltrim;
use function Pipeline\take;
use function Safe\file_get_contents;
use function Safe\json_encode;
use function strlen;
use function substr;
use const JSON_PRETTY_PRINT;
use const PHP_EOL;

/**
 * @internal
 */
final class StrykerReportFactory
{
    private const DETECTION_STATUS_MAP = [
        MutantProcess::CODE_KILLED => 'Killed',
        MutantProcess::CODE_ESCAPED => 'Survived',
        MutantProcess::CODE_ERROR => 'RuntimeError',
        MutantProcess::CODE_TIMED_OUT => 'Timeout',
        MutantProcess::CODE_NOT_COVERED => 'NoCoverage',
    ];

    public function create(MetricsCalculator $calculator): string
    {
        $files = [];

        if ($calculator->getTotalMutantsCount() !== 0) {
            $resultsByPath = self::retrieveResultsByPath($calculator);
            $basePath = Path::getLongestCommonBasePath(array_keys($resultsByPath));

            $files = self::retrieveFiles($resultsByPath, $basePath);
        }

        return json_encode(
            [
                'schemaVersion' => 1,
                'mutationScore' => $calculator->getMutationScoreIndicator(),
                'thresholds' => [
                    'low' => 20,
                    'high' => 80,
                ],
                'files' => $files,
            ]
        );
    }

    /**
     * @param  array<string, MutantExecutionResult[]> $resultsByPath
     */
    private static function retrieveFiles(array $resultsByPath, string $basePath): array
    {
        $files = [];

        foreach ($resultsByPath as $path => $results) {
            $relativePath = $path === $basePath ? $path : Path::makeRelative($path, $basePath);

            $result = current($results);
            Assert::isInstanceOf($result, MutantExecutionResult::class);

            $files[$relativePath] = [
                'language' => 'php',
                'source' => file_get_contents($path),
                'mutants' =>self::retrieveMutants($results)
            ];
        }

        return $files;
    }

    /**
     * @return array<string, MutantExecutionResult[]>
     */
    private static function retrieveResultsByPath(MetricsCalculator $calculator): array
    {
        $results = [];

        foreach ($calculator->getAllExecutionResults() as $result) {
            $results[$result->getOriginalFilePath()][] = $result;
        }

        return $results;
    }

    /**
     * @param MutantExecutionResult[] $results
     */
    private static function retrieveMutants(array $results): array
    {
        return array_map(
            static function (MutantExecutionResult $result): array {
                $replacement = self::retrieveReplacementFromDiff($result->getMutationDiff());

                $lTrimedReplacement = ltrim($replacement);
                $replacementLength = strlen($replacement) + 1;

                $startingColumn = $replacementLength - strlen($lTrimedReplacement);
                $endingColumn = $replacementLength + 1;

                return [
                    'id' => $result->getMutationHash(),
                    'mutatorName' => $result->getMutatorName(),
                    'replacement' => $replacement,
                    'description' => 'yo',
                    'location' => [
                        'start' => [
                            'line' => $result->getOriginalStartingLine(),
                            'column' => $startingColumn,
                        ],
                        'end' => [
                            'line' => $result->getOriginalEndingLine(),
                            'column' => $endingColumn,
                        ],
                    ],
                    'status' => self::DETECTION_STATUS_MAP[$result->getProcessResultCode()],
                ];
            },
            $results
        );
    }

    private static function retrieveReplacementFromDiff(string $diff): string
    {
        $lines = explode(PHP_EOL, $diff);

        $lines = array_map(
            static function (string $line): string {
                return isset($line[0]) ? substr($line, 1) : $line;
            },
            array_filter(
                array_slice($lines, 2),
                static function (string $line): bool {
                    return isset($line[0]) && strpos($line, '+') === 0;
                }
            )
        );

        return implode(PHP_EOL, $lines);
    }
}
