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

namespace Infection\Configuration\Entry;

/**
 * @internal
 * @final
 */
class Logs
{
    private ?string $textLogFilePath;
    private ?string $summaryLogFilePath;
    private ?string $jsonLogFilePath;
    private ?string $debugLogFilePath;
    private ?string $perMutatorFilePath;
    private bool $useGitHubAnnotationsLogger;
    private ?Badge $badge;

    public function __construct(
        ?string $textLogFilePath,
        ?string $summaryLogFilePath,
        ?string $jsonLogFilePath,
        ?string $debugLogFilePath,
        ?string $perMutatorFilePath,
        bool $useGitHubAnnotationsLogger,
        ?Badge $badge
    ) {
        $this->textLogFilePath = $textLogFilePath;
        $this->summaryLogFilePath = $summaryLogFilePath;
        $this->jsonLogFilePath = $jsonLogFilePath;
        $this->debugLogFilePath = $debugLogFilePath;
        $this->perMutatorFilePath = $perMutatorFilePath;
        $this->useGitHubAnnotationsLogger = $useGitHubAnnotationsLogger;
        $this->badge = $badge;
    }

    public static function createEmpty(): self
    {
        return new self(
            null,
            null,
            null,
            null,
            null,
            false,
            null
        );
    }

    public function getTextLogFilePath(): ?string
    {
        return $this->textLogFilePath;
    }

    public function getSummaryLogFilePath(): ?string
    {
        return $this->summaryLogFilePath;
    }

    public function getJsonLogFilePath(): ?string
    {
        return $this->jsonLogFilePath;
    }

    public function getDebugLogFilePath(): ?string
    {
        return $this->debugLogFilePath;
    }

    public function getPerMutatorFilePath(): ?string
    {
        return $this->perMutatorFilePath;
    }

    public function setUseGitHubAnnotationsLogger(bool $useGitHubAnnotationsLogger): void
    {
        $this->useGitHubAnnotationsLogger = $useGitHubAnnotationsLogger;
    }

    public function getUseGitHubAnnotationsLogger(): bool
    {
        return $this->useGitHubAnnotationsLogger;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }
}
