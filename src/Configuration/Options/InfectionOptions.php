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

use JMS\Serializer\Annotation as Serializer;

/**
 * Root configuration options for Infection.
 *
 * @internal
 */
final class InfectionOptions
{
    /**
     * @param array<string, mixed> $mutators
     */
    public function __construct(
        #[Serializer\Type(SourceOptions::class)]
        public readonly SourceOptions $source,
        #[Serializer\Type('float')]
        public readonly ?float $timeout = null,
        #[Serializer\Type('int_or_string')]
        public readonly int|string|null $threads = null,
        #[Serializer\Type(LogsOptions::class)]
        public readonly ?LogsOptions $logs = null,
        #[Serializer\Type('string')]
        public readonly ?string $tmpDir = null,
        #[Serializer\Type(PhpUnitOptions::class)]
        public readonly ?PhpUnitOptions $phpUnit = null,
        #[Serializer\Type(PhpStanOptions::class)]
        public readonly ?PhpStanOptions $phpStan = null,
        #[Serializer\Type('bool')]
        public readonly ?bool $ignoreMsiWithNoMutations = null,
        #[Serializer\Type('float')]
        public readonly ?float $minMsi = null,
        #[Serializer\Type('float')]
        public readonly ?float $minCoveredMsi = null,
        #[Serializer\Type('array')]
        public readonly array $mutators = [],
        #[Serializer\Type('string')]
        public readonly ?string $testFramework = null,
        #[Serializer\Type('string')]
        public readonly ?string $staticAnalysisTool = null,
        #[Serializer\Type('string')]
        public readonly ?string $staticAnalysisToolOptions = null,
        #[Serializer\Type('string')]
        public readonly ?string $bootstrap = null,
        #[Serializer\Type('string')]
        public readonly ?string $initialTestsPhpOptions = null,
        #[Serializer\Type('string')]
        public readonly ?string $testFrameworkOptions = null,
    ) {
    }
}
