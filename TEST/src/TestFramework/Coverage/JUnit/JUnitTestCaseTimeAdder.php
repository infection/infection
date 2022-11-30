<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use function array_key_exists;
use function array_sum;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use function _HumbugBox9658796bb9f0\Safe\substr;
use function strpos;
final class JUnitTestCaseTimeAdder
{
    public function __construct(private array $tests)
    {
    }
    public function getTotalTestTime() : float
    {
        return array_sum($this->uniqueTestLocations());
    }
    private function uniqueTestLocations() : array
    {
        $seenTestSuites = [];
        foreach ($this->tests as $testLocation) {
            $methodName = $testLocation->getMethod();
            $methodSeparatorPos = strpos($methodName, '::');
            if ($methodSeparatorPos === \false) {
                continue;
            }
            $testSuiteName = substr($methodName, 0, $methodSeparatorPos);
            if (array_key_exists($testSuiteName, $seenTestSuites)) {
                continue;
            }
            $seenTestSuites[$testSuiteName] = $testLocation->getExecutionTime();
        }
        return $seenTestSuites;
    }
}
