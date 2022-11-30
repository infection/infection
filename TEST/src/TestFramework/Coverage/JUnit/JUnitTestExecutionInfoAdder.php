<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use function explode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
class JUnitTestExecutionInfoAdder
{
    public function __construct(private TestFrameworkAdapter $adapter, private TestFileDataProvider $testFileDataProvider)
    {
    }
    public function addTestExecutionInfo(iterable $traces) : iterable
    {
        if (!$this->adapter->hasJUnitReport()) {
            return $traces;
        }
        return $this->testExecutionInfoAdder($traces);
    }
    private function testExecutionInfoAdder(iterable $traces) : iterable
    {
        foreach ($traces as $trace) {
            $tests = $trace->getTests();
            if ($tests === null) {
                continue;
            }
            foreach ($tests->getTestsLocationsBySourceLine() as &$testsLocations) {
                foreach ($testsLocations as $line => $test) {
                    $testsLocations[$line] = $this->createCompleteTestLocation($test);
                }
            }
            unset($testsLocations);
            (yield $trace);
        }
    }
    private function createCompleteTestLocation(TestLocation $test) : TestLocation
    {
        $class = explode(':', $test->getMethod(), 2)[0];
        $testFileData = $this->testFileDataProvider->getTestFileInfo($class);
        return new TestLocation($test->getMethod(), $testFileData->path, $testFileData->time);
    }
}
