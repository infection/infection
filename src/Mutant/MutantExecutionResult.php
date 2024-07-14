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

namespace Infection\Mutant;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\MutatorResolver;
use Later\Interfaces\Deferred;
use RuntimeException;
use function sprintf;
use function strlen;
use function strrpos;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class MutantExecutionResult
{
    private readonly string $detectionStatus;
    private readonly string $mutatorClass;

    /**
     * @param Deferred<string> $mutantDiff
     * @param Deferred<string> $originalCode
     * @param Deferred<string> $mutatedCode
     * @param TestLocation[] $tests
     */
    public function __construct(
        private readonly string $processCommandLine,
        private readonly string $processOutput,
        string $detectionStatus,
        private readonly Deferred $mutantDiff,
        private readonly string $mutantHash,
        string $mutatorClass,
        private readonly string $mutatorName,
        private readonly string $originalFilePath,
        private readonly int $originalStartingLine,
        private readonly int $originalEndingLine,
        private readonly int $originalStartFilePosition,
        private readonly int $originalEndFilePosition,
        private readonly Deferred $originalCode,
        private readonly Deferred $mutatedCode,
        private readonly array $tests,
    ) {
        Assert::oneOf($detectionStatus, DetectionStatus::ALL);
        Assert::true(MutatorResolver::isValidMutator($mutatorClass), sprintf('Unknown mutator "%s"', $mutatorClass));

        $this->detectionStatus = $detectionStatus;
        $this->mutatorClass = $mutatorClass;
    }

    public static function createFromNonCoveredMutant(Mutant $mutant): self
    {
        return self::createFromMutant($mutant, DetectionStatus::NOT_COVERED);
    }

    public static function createFromTimeSkippedMutant(Mutant $mutant): self
    {
        return self::createFromMutant($mutant, DetectionStatus::SKIPPED);
    }

    public static function createFromIgnoredMutant(Mutant $mutant): self
    {
        return self::createFromMutant($mutant, DetectionStatus::IGNORED);
    }

    public function getProcessCommandLine(): string
    {
        return $this->processCommandLine;
    }

    public function getProcessOutput(): string
    {
        return $this->processOutput;
    }

    public function getDetectionStatus(): string
    {
        return $this->detectionStatus;
    }

    public function getMutantDiff(): string
    {
        return $this->mutantDiff->get();
    }

    public function getMutantHash(): string
    {
        return $this->mutantHash;
    }

    public function getMutatorClass(): string
    {
        return $this->mutatorClass;
    }

    public function getMutatorName(): string
    {
        return $this->mutatorName;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    public function getOriginalStartingLine(): int
    {
        return $this->originalStartingLine;
    }

    public function getOriginalEndingLine(): int
    {
        return $this->originalEndingLine;
    }

    public function getOriginalStartingColumn(string $originalCode): int
    {
        return $this->toColumn($originalCode, $this->originalStartFilePosition);
    }

    public function getOriginalEndingColumn(string $originalCode): int
    {
        return $this->toColumn($originalCode, $this->originalEndFilePosition);
    }

    public function getOriginalCode(): string
    {
        return $this->originalCode->get();
    }

    public function getMutatedCode(): string
    {
        return $this->mutatedCode->get();
    }

    /**
     * @return TestLocation[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * Adopted from https://github.com/nikic/PHP-Parser/blob/4abdcde5f16269959a834e4e58ea0ba0938ab133/lib/PhpParser/Error.php#L155
     */
    private function toColumn(string $code, int $position): int
    {
        if ($position > strlen($code)) {
            throw new RuntimeException('Invalid position information');
        }

        $lineStartPos = strrpos($code, "\n", $position - strlen($code));

        if ($lineStartPos === false) {
            $lineStartPos = -1;
        }

        return $position - $lineStartPos;
    }

    private static function createFromMutant(Mutant $mutant, string $detectionStatus): self
    {
        $mutation = $mutant->getMutation();

        return new self(
            '',
            '',
            $detectionStatus,
            $mutant->getDiff(),
            $mutant->getMutation()->getHash(),
            $mutant->getMutation()->getMutatorClass(),
            $mutant->getMutation()->getMutatorName(),
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalStartingLine(),
            $mutation->getOriginalEndingLine(),
            $mutation->getOriginalStartFilePosition(),
            $mutation->getOriginalEndFilePosition(),
            $mutant->getPrettyPrintedOriginalCode(),
            $mutant->getMutatedCode(),
            $mutant->getTests(),
        );
    }
}
