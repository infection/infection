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

use function array_flip;
use function array_keys;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Console\LogVerbosity;
use Infection\Metrics\TargetDetectionStatusesProvider;
use Infection\Mutant\DetectionStatus;
use function ksort;
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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::DEBUG, false, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, false);

        $this->assertProvidesExcluding(
            [
                DetectionStatus::KILLED,
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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

        $this->assertProvidesExcluding(
            [
                DetectionStatus::KILLED,
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                DetectionStatus::ERROR,
                DetectionStatus::NOT_COVERED,
            ],
            $provider->get(),
        );
    }

    public function test_it_includes_escaped_when_requested(): void
    {
        $logs = $this->createMock(Logs::class);

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, true);

        $this->assertProvides([
            DetectionStatus::ESCAPED,
        ], $provider->get());
    }

    public function test_it_provides_nothing_when_logging_verbosity_is_none(): void
    {
        $logs = $this->createMock(Logs::class);

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, false, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NONE, true, false);

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

        $provider = new TargetDetectionStatusesProvider($logs, LogVerbosity::NORMAL, true, false);

        $this->assertSame([], $provider->get());
    }

    private function assertProvides(array $expected, array $actual): void
    {
        $expected = array_flip($expected);
        ksort($expected);

        ksort($actual);

        $this->assertSame(array_keys($expected), array_keys($actual));
    }

    private function assertProvidesExcluding(array $excluding, array $actual): void
    {
        $expected = $this->getDetectionStatusesIndexExcluding($excluding);
        ksort($expected);

        ksort($actual);

        $this->assertSame(array_keys($expected), array_keys($actual));
    }

    private function getDetectionStatusesIndexExcluding(array $excludeList): array
    {
        $detectionStatuses = array_flip(DetectionStatus::ALL);

        foreach ($excludeList as $exclude) {
            unset($detectionStatuses[$exclude]);
        }

        return $detectionStatuses;
    }
}
