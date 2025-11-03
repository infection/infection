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

namespace Infection\Tests\Configuration\Entry;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;

final class LogsBuilder
{
    private function __construct(
        private ?string $textLogFilePath,
        private ?string $htmlLogFilePath,
        private ?string $summaryLogFilePath,
        private ?string $jsonLogFilePath,
        private ?string $gitlabLogFilePath,
        private ?string $debugLogFilePath,
        private ?string $perMutatorFilePath,
        private bool $useGitHubAnnotationsLogger,
        private ?StrykerConfig $strykerConfig,
        private ?string $summaryJsonLogFilePath,
    ) {
    }

    public static function from(Logs $logs): self
    {
        return new self(
            $logs->getTextLogFilePath(),
            $logs->getHtmlLogFilePath(),
            $logs->getSummaryLogFilePath(),
            $logs->getJsonLogFilePath(),
            $logs->getGitlabLogFilePath(),
            $logs->getDebugLogFilePath(),
            $logs->getPerMutatorFilePath(),
            $logs->getUseGitHubAnnotationsLogger(),
            $logs->getStrykerConfig(),
            $logs->getSummaryJsonLogFilePath(),
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            textLogFilePath: null,
            htmlLogFilePath: null,
            summaryLogFilePath: null,
            jsonLogFilePath: null,
            gitlabLogFilePath: null,
            debugLogFilePath: null,
            perMutatorFilePath: null,
            useGitHubAnnotationsLogger: false,
            strykerConfig: null,
            summaryJsonLogFilePath: null,
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            textLogFilePath: '/var/log/infection/text.log',
            htmlLogFilePath: '/var/log/infection/report.html',
            summaryLogFilePath: '/var/log/infection/summary.log',
            jsonLogFilePath: '/var/log/infection/infection.json',
            gitlabLogFilePath: '/var/log/infection/gitlab.json',
            debugLogFilePath: '/var/log/infection/debug.log',
            perMutatorFilePath: '/var/log/infection/per-mutator.md',
            useGitHubAnnotationsLogger: true,
            strykerConfig: StrykerConfig::forFullReport('master'),
            summaryJsonLogFilePath: '/var/log/infection/summary.json',
        );
    }

    public function withTextLogFilePath(?string $textLogFilePath): self
    {
        $clone = clone $this;
        $clone->textLogFilePath = $textLogFilePath;

        return $clone;
    }

    public function withHtmlLogFilePath(?string $htmlLogFilePath): self
    {
        $clone = clone $this;
        $clone->htmlLogFilePath = $htmlLogFilePath;

        return $clone;
    }

    public function withSummaryLogFilePath(?string $summaryLogFilePath): self
    {
        $clone = clone $this;
        $clone->summaryLogFilePath = $summaryLogFilePath;

        return $clone;
    }

    public function withJsonLogFilePath(?string $jsonLogFilePath): self
    {
        $clone = clone $this;
        $clone->jsonLogFilePath = $jsonLogFilePath;

        return $clone;
    }

    public function withGitlabLogFilePath(?string $gitlabLogFilePath): self
    {
        $clone = clone $this;
        $clone->gitlabLogFilePath = $gitlabLogFilePath;

        return $clone;
    }

    public function withDebugLogFilePath(?string $debugLogFilePath): self
    {
        $clone = clone $this;
        $clone->debugLogFilePath = $debugLogFilePath;

        return $clone;
    }

    public function withPerMutatorFilePath(?string $perMutatorFilePath): self
    {
        $clone = clone $this;
        $clone->perMutatorFilePath = $perMutatorFilePath;

        return $clone;
    }

    public function withUseGitHubAnnotationsLogger(bool $useGitHubAnnotationsLogger): self
    {
        $clone = clone $this;
        $clone->useGitHubAnnotationsLogger = $useGitHubAnnotationsLogger;

        return $clone;
    }

    public function withStrykerConfig(?StrykerConfig $strykerConfig): self
    {
        $clone = clone $this;
        $clone->strykerConfig = $strykerConfig;

        return $clone;
    }

    public function withSummaryJsonLogFilePath(?string $summaryJsonLogFilePath): self
    {
        $clone = clone $this;
        $clone->summaryJsonLogFilePath = $summaryJsonLogFilePath;

        return $clone;
    }

    public function build(): Logs
    {
        return new Logs(
            $this->textLogFilePath,
            $this->htmlLogFilePath,
            $this->summaryLogFilePath,
            $this->jsonLogFilePath,
            $this->gitlabLogFilePath,
            $this->debugLogFilePath,
            $this->perMutatorFilePath,
            $this->useGitHubAnnotationsLogger,
            $this->strykerConfig,
            $this->summaryJsonLogFilePath,
        );
    }
}
