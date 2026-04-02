<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Tracing\Tracer;

use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\TestFramework\Coverage\PHPUnitXml\PHPUnitXmlReportFactory;
use Infection\TestFramework\PhpUnit\PHPUnitCoverageTracer;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\TestFramework\Tracing\Trace\TraceAssertion;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversNothing]
final class TracerIntegrationTest extends TestCase
{
    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        string $indexXmlPath,
        ?string $junitXmlPath,
        Trace $expected,
    ): void {
        $tracer = $this->createTracer(
            $indexXmlPath,
            $junitXmlPath,
        );

        $actual = $tracer->trace(
            $expected->getSourceFileInfo(),
        );

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            PhpUnit09Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            PhpUnit10Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            PhpUnit11Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.0] ',
            PhpUnit120Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.5] ',
            PhpUnit125Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[Codeception] ',
            CodeceptionProvider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PhpSpec] ',
            PhpSpecProvider::infoProvider(),
        );
    }

    private function createTracer(
        string $indexXmlPath,
        ?string $junitXmlPath,
    ): Tracer {
        if ($junitXmlPath === null) {
            $this->markTestIncomplete('TODO');
        }

        return new PHPUnitCoverageTracer(
            new PHPUnitXmlReportFactory(
                indexReportLocator: new FixedLocator($indexXmlPath),
                jUnitReportLocator: new FixedLocator($junitXmlPath),
            ),
        );
    }
}
