<?php

namespace Infection\Tests\TestFramework\Tracing;

use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlProvider;
use Infection\TestFramework\Tracing\PHPUnitCoverageTracer;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\IndexReportLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(PHPUnitCoverageTracer::class)]
final class PHPUnitCoverageTracerTest extends TestCase
{
    private PHPUnitCoverageTracer $tracer;

    protected function setUp(): void
    {
        $this->tracer = new PHPUnitCoverageTracer(
            new PHPUnitXmlProvider(
                new IndexReportLocator(),
                new JUnitReportLocator(),
            ),
        );
    }

    public function test_it_can_create_a_trace(
        SplFileInfo $fileInfo,
        Trace $expected,
    ): void
    {
        $actual = $this->tracer->trace($fileInfo);

        TraceAssertion::assertEquals($expected, $actual);
    }
}
