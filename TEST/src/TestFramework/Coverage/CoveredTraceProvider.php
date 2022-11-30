<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use _HumbugBox9658796bb9f0\Infection\FileSystem\FileFilter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
final class CoveredTraceProvider implements TraceProvider
{
    public function __construct(private TraceProvider $primaryTraceProvider, private JUnitTestExecutionInfoAdder $testFileDataAdder, private FileFilter $bufferedFilter)
    {
    }
    public function provideTraces() : iterable
    {
        $filteredTraces = $this->bufferedFilter->filter($this->primaryTraceProvider->provideTraces());
        return $this->testFileDataAdder->addTestExecutionInfo($filteredTraces);
    }
}
