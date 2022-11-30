<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

final class UncoveredTraceProvider implements TraceProvider
{
    public function __construct(private BufferedSourceFileFilter $bufferedFilter)
    {
    }
    public function provideTraces() : iterable
    {
        foreach ($this->bufferedFilter->getUnseenInCoverageReportFiles() as $splFileInfo) {
            (yield new ProxyTrace($splFileInfo, null));
        }
    }
}
