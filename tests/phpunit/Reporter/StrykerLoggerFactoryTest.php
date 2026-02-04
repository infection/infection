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

namespace Infection\Tests\Reporter;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Reporter\Html\StrykerHtmlReportBuilder;
use Infection\Reporter\StrykerLoggerFactory;
use Infection\Reporter\StrykerReporter;
use Infection\Tests\Fixtures\FakeCiDetector;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(StrykerLoggerFactory::class)]
final class StrykerLoggerFactoryTest extends TestCase
{
    public function test_it_does_not_create_any_reporter_for_no_verbosity_level_and_no_badge(): void
    {
        $factory = $this->createReporterFactory();

        $reporter = $factory->createFromLogEntries(
            new Logs(
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                true,
                null,
                '/a/file',
            ),
        );

        $this->assertNull($reporter);
    }

    public function test_it_creates_a_stryker_reporter_on_no_verbosity(): void
    {
        $factory = $this->createReporterFactory();

        $reporter = $factory->createFromLogEntries(
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                StrykerConfig::forBadge('master'),
                null,
            ),
        );

        $this->assertInstanceOf(StrykerReporter::class, $reporter);
    }

    #[DataProvider('configProvider')]
    public function test_it_creates_a_reporter_for_log_type_on_normal_verbosity(
        Logs $logs,
        ?string $expectedLogger,
    ): void {
        $factory = $this->createReporterFactory();

        $reporter = $factory->createFromLogEntries($logs);

        if ($expectedLogger === null) {
            $this->assertNull($reporter);

            return;
        }

        $this->assertInstanceOf($expectedLogger, $reporter);
    }

    public static function configProvider(): iterable
    {
        yield 'no reporter' => [
            Logs::createEmpty(),
            null,
        ];

        yield 'stryker for badge reporter' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                StrykerConfig::forBadge('foo'),
                null,
            ),
            StrykerReporter::class,
        ];

        yield 'stryker reporter' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                StrykerConfig::forFullReport('foo'),
                null,
            ),
            StrykerReporter::class,
        ];

        yield 'all reporters' => [
            new Logs(
                'text',
                'html',
                'summary',
                'json',
                'gitlab',
                'debug',
                'per_mutator',
                true,
                StrykerConfig::forBadge('branch'),
                'summary_json',
            ),
            StrykerReporter::class,
        ];
    }

    private function createReporterFactory(): StrykerLoggerFactory
    {
        $metricsCalculator = new MetricsCalculator(2);

        return new StrykerLoggerFactory(
            new MetricsCalculator(2),
            new StrykerHtmlReportBuilder($metricsCalculator, new ResultsCollector()),
            new FakeCiDetector(),
            new FakeLogger(),
        );
    }
}
