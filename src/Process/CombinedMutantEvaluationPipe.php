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

namespace Infection\Process;

use function array_key_exists;
use Infection\Mutant\DetectionStatus;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Webmozart\Assert\Assert;

/** @internal */
final class CombinedMutantEvaluationPipe implements MutantEvaluationPipe
{
    private int $currentPipeIndex = 0;

    /**
     * @param non-empty-list<MutantEvaluationPipe> $pipes
     */
    private function __construct(
        private readonly array $pipes,
    ) {
    }

    public static function from(MutantEvaluationPipe $first, MutantEvaluationPipe $second): self
    {
        self::assertIsCold($first);
        self::assertIsCold($second);

        return new self([$first, $second]);
    }

    public function isCold(): bool
    {
        foreach ($this->pipes as $pipe) {
            if (!$pipe->isCold()) {
                return false;
            }
        }

        return true;
    }

    public function getCurrent(): MutantProcess
    {
        return $this->getCurrentPipe()->getCurrent();
    }

    public function hasNext(): bool
    {
        $currentPipe = $this->getCurrentPipe();

        if ($currentPipe->hasNext()) {
            return true;
        }

        return array_key_exists($this->currentPipeIndex + 1, $this->pipes)
            && $currentPipe->getCurrent()->getMutantExecutionResult()->getDetectionStatus() === DetectionStatus::ESCAPED;
    }

    public function createNext(): MutantProcess
    {
        $currentPipe = $this->getCurrentPipe();

        if ($currentPipe->hasNext()) {
            return $currentPipe->createNext();
        }

        ++$this->currentPipeIndex;

        return $this->getCurrent();
    }

    public function merge(MutantEvaluationPipe $other): MutantEvaluationPipe
    {
        self::assertIsCold($this);
        self::assertIsCold($other);

        return new self([$this, $other]);
    }

    private static function assertIsCold(MutantEvaluationPipe $pipe): void
    {
        Assert::true(
            $pipe->isCold(),
            'Cannot merge a mutant evaluation pipe after evaluation has started.',
        );
    }

    private function getCurrentPipe(): MutantEvaluationPipe
    {
        return $this->pipes[$this->currentPipeIndex];
    }
}
