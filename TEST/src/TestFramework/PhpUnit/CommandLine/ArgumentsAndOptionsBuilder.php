<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\CommandLine;

use function array_key_exists;
use function array_map;
use function array_merge;
use function count;
use function end;
use function explode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use function ltrim;
use function preg_quote;
use function rtrim;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    public function __construct(private bool $executeOnlyCoveringTestCases)
    {
    }
    public function buildForInitialTestsRun(string $configPath, string $extraOptions) : array
    {
        $options = ['--configuration', $configPath];
        if ($extraOptions !== '') {
            $options = array_merge($options, array_map(static fn($option): string => '--' . $option, explode(' --', ltrim($extraOptions, '-'))));
        }
        return $options;
    }
    public function buildForMutant(string $configPath, string $extraOptions, array $tests) : array
    {
        $options = $this->buildForInitialTestsRun($configPath, $extraOptions);
        if ($this->executeOnlyCoveringTestCases && count($tests) > 0) {
            $filterString = '/';
            $usedTestCases = [];
            foreach ($tests as $testLocation) {
                $testCaseString = $testLocation->getMethod();
                $partsDelimitedByColons = explode('::', $testCaseString, 2);
                if (count($partsDelimitedByColons) > 1) {
                    $parts = explode('\\', $partsDelimitedByColons[0]);
                    $testCaseString = sprintf('%s::%s', end($parts), $partsDelimitedByColons[1]);
                }
                if (array_key_exists($testCaseString, $usedTestCases)) {
                    continue;
                }
                $usedTestCases[$testCaseString] = \true;
                $filterString .= preg_quote($testCaseString, '/') . '|';
            }
            $filterString = rtrim($filterString, '|') . '/';
            $options[] = '--filter';
            $options[] = $filterString;
        }
        return $options;
    }
}
