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

namespace Infection\Tests\Metrics;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Console\LogVerbosity;
use Infection\Metrics\TargetDetectionStatusesProvider;
use Infection\Mutant\DetectionStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TargetDetectionStatusesProvider::class)]
final class TargetDetectionStatusesProviderTest extends TestCase
{
    public function test_it_provides_all_statuses_when_debugging_log_is_enabled(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getDebugLogFilePath')
            ->willReturn('debug.log')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_provides_all_statuses_when_per_mutator_report_is_expected(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getPerMutatorFilePath')
            ->willReturn('per_mutator.md')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_provides_all_statuses_when_debugging_is_enabled_for_text_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getTextLogFilePath')
            ->willReturn('infection.log')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::DEBUG, false, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_ignores_some_statuses_when_debugging_is_not_enabled_for_text_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getTextLogFilePath')
            ->willReturn('infection.log')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, 0);

        $this->assertProvidesExcluding(
            [
                DetectionStatus::KILLED_BY_TESTS,
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                DetectionStatus::ERROR,
            ],
            $provider->get(),
        );
    }

    public function test_it_ignores_more_statuses_when_running_in_only_covered_mode_for_text_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getTextLogFilePath')
            ->willReturn('infection.log')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvidesExcluding(
            [
                DetectionStatus::KILLED_BY_TESTS,
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                DetectionStatus::ERROR,
                DetectionStatus::NOT_COVERED,
            ],
            $provider->get(),
        );
    }

    public function test_it_provides_not_covered_when_with_uncovered_option_is_used(): void
    {
        $logs = $this->createMock(Logs::class);

        $provider = new TargetDetectionStatusesProvider(
            $logs,
            logVerbosity: LogVerbosity::NORMAL,
            onlyCoveredMode: false,
            numberOfShownMutations: 0,
        );

        $this->assertProvides(
            [
                DetectionStatus::NOT_COVERED,
            ],
            $provider->get(),
        );
    }

    public function test_it_includes_escaped_when_requested(): void
    {
        $logs = $this->createMock(Logs::class);

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, 20);

        $this->assertProvides([
            DetectionStatus::ESCAPED,
        ], $provider->get());
    }

    public function test_it_provides_nothing_when_logging_verbosity_is_none(): void
    {
        $logs = $this->createMock(Logs::class);

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, 0);

        $this->assertSame([], $provider->get());
    }

    public function test_it_provides_escaped_when_using_github_annotations_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getUseGitHubAnnotationsLogger')
            ->willReturn(true)
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvides([
            DetectionStatus::ESCAPED,
        ], $provider->get());
    }

    public function test_it_provides_escaped_when_using_gitlab_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getGitlabLogFilePath')
            ->willReturn('gitlab.json')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvides([
            DetectionStatus::ESCAPED,
        ], $provider->get());
    }

    public function test_it_provides_certain_statuses_for_json_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getJsonLogFilePath')
            ->willReturn('infection.json')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvidesExcluding([
            DetectionStatus::NOT_COVERED,
            DetectionStatus::SKIPPED,
        ], $provider->get());
    }

    public function test_it_provides_certain_statuses_including_not_covered_for_json_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getJsonLogFilePath')
            ->willReturn('infection.json')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, 0);

        $this->assertProvidesExcluding([
            DetectionStatus::SKIPPED,
        ], $provider->get());
    }

    public function test_it_provides_all_statuses_for_html_logger(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getHtmlLogFilePath')
            ->willReturn('infection.html')
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_provides_all_statuses_for_full_stryker_report(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->never())
            ->method('getHtmlLogFilePath')
            ->willReturn(null)
        ;
        $logs
            ->expects($this->once())
            ->method('getStrykerConfig')
            ->willReturn(StrykerConfig::forFullReport('master'))
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_provides_all_statuses_for_full_stryker_report_with_verbosity_none(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->never())
            ->method('getHtmlLogFilePath')
            ->willReturn(null)
        ;
        $logs
            ->expects($this->once())
            ->method('getStrykerConfig')
            ->willReturn(StrykerConfig::forFullReport('master'))
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, 0);

        $this->assertProvidesExcluding([], $provider->get());
    }

    public function test_it_provides_nothing_for_stryker_badge_report(): void
    {
        $logs = $this->createMock(Logs::class);
        $logs
            ->expects($this->once())
            ->method('getHtmlLogFilePath')
            ->willReturn(null)
        ;
        $logs
            ->expects($this->once())
            ->method('getStrykerConfig')
            ->willReturn(StrykerConfig::forBadge('master'))
        ;

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, 0);

        $this->assertSame([], $provider->get());
    }

    /**
     * @param DetectionStatus[] $expected
     * @param DetectionStatus[] $actual
     */
    private function assertProvides(array $expected, array $actual): void
    {
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @param DetectionStatus[] $excluding
     * @param DetectionStatus[] $actual
     */
    private function assertProvidesExcluding(array $excluding, array $actual): void
    {
        $expected = DetectionStatus::getCasesExcluding(...$excluding);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }
}
