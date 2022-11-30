<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use function dirname;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\TraceProvider;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
class PhpUnitXmlCoverageTraceProvider implements TraceProvider
{
    public function __construct(private IndexXmlCoverageLocator $indexLocator, private IndexXmlCoverageParser $indexParser, private XmlCoverageParser $parser)
    {
    }
    public function provideTraces() : iterable
    {
        $indexPath = $this->indexLocator->locate();
        $coverageBasePath = dirname($indexPath);
        $indexContents = file_get_contents($indexPath);
        foreach ($this->indexParser->parse($indexPath, $indexContents, $coverageBasePath) as $infoProvider) {
            (yield $this->parser->parse($infoProvider));
        }
    }
}
