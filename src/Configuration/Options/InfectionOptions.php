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

namespace Infection\Configuration\Options;

use Infection\Configuration\ConfigurationInterface;
use function is_int;
use JMS\Serializer\Annotation as Serializer;

/**
 * Root configuration options for Infection.
 *
 * @internal
 */
final class InfectionOptions implements ConfigurationInterface
{
    /**
     * Default timeout in seconds (from ConfigurationFactory::DEFAULT_TIMEOUT)
     */
    private const DEFAULT_TIMEOUT = 10.0;

    /**
     * @param array<string, mixed> $mutators
     */
    public function __construct(
        #[Serializer\Type(SourceOptions::class)]
        public SourceOptions $source,
        #[Serializer\Type('float')]
        public ?float $timeout = self::DEFAULT_TIMEOUT,
        #[Serializer\Type('scalar_or_object')]
        public int|string|null $threads = 1,
        #[Serializer\Type(LogsOptions::class)]
        public ?LogsOptions $logs = null,
        #[Serializer\Type('string')]
        public ?string $tmpDir = null,
        #[Serializer\Type(PhpUnitOptions::class)]
        public ?PhpUnitOptions $phpUnit = null,
        #[Serializer\Type(PhpStanOptions::class)]
        public ?PhpStanOptions $phpStan = null,
        #[Serializer\Type('bool')]
        public ?bool $ignoreMsiWithNoMutations = null,
        #[Serializer\Type('float')]
        public ?float $minMsi = null,
        #[Serializer\Type('float')]
        public ?float $minCoveredMsi = null,
        #[Serializer\Type('array')]
        public array $mutators = ['@default' => true],
        #[Serializer\Type('string')]
        public ?string $testFramework = 'phpunit',
        #[Serializer\Type('string')]
        public ?string $staticAnalysisTool = null,
        #[Serializer\Type('string')]
        public ?string $staticAnalysisToolOptions = null,
        #[Serializer\Type('string')]
        public ?string $bootstrap = null,
        #[Serializer\Type('string')]
        public ?string $initialTestsPhpOptions = null,
        #[Serializer\Type('string')]
        public ?string $testFrameworkOptions = null,
        // CLI-only options (not in JSON schema)
        public bool $dryRun = false,
        public int $msiPrecision = 2,
    ) {
    }

    public function getProcessTimeout(): float
    {
        return $this->timeout ?? self::DEFAULT_TIMEOUT;
    }

    public function getThreadCount(): int
    {
        if (is_int($this->threads)) {
            return $this->threads;
        }

        // Default when threads is null or "max" (max is resolved elsewhere)
        return 1;
    }

    public function getMsiPrecision(): int
    {
        return $this->msiPrecision;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }
}
