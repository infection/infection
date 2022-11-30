<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use function array_values;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\NodeLineRangeData;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\SourceMethodLineRange;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\TestLocations;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class TestLocator
{
    public function __construct(private TestLocations $testLocations)
    {
    }
    public function hasTests() : bool
    {
        foreach ($this->testLocations->getTestsLocationsBySourceLine() as $testLocations) {
            if ($testLocations !== []) {
                return \true;
            }
        }
        return \false;
    }
    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature) : iterable
    {
        if ($isOnFunctionSignature) {
            return $this->getTestsForFunctionSignature($lineRange);
        }
        return $this->getTestsForLineRange($lineRange);
    }
    private function getTestsForFunctionSignature(NodeLineRangeData $lineRange) : iterable
    {
        Assert::count($lineRange->range, 1);
        yield from $this->getTestsForExecutedMethodOnLine($lineRange->range[0]);
    }
    private function getTestsForLineRange(NodeLineRangeData $lineRange) : iterable
    {
        $uniqueTestLocations = [];
        foreach ($lineRange->range as $line) {
            foreach ($this->testLocations->getTestsLocationsBySourceLine()[$line] ?? [] as $testLocation) {
                $uniqueTestLocations[$testLocation->getMethod()] = $testLocation;
            }
        }
        yield from array_values($uniqueTestLocations);
    }
    private function getTestsForExecutedMethodOnLine(int $line) : iterable
    {
        foreach ($this->testLocations->getSourceMethodRangeByMethod() as $methodRange) {
            if ($line >= $methodRange->getStartLine() && $line <= $methodRange->getEndLine()) {
                return $this->getTestsForLineRange(new NodeLineRangeData($methodRange->getStartLine(), $methodRange->getEndLine()));
            }
        }
        return [];
    }
}
