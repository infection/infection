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

namespace Infection\TestFramework\Common;

use function array_key_exists;
use function array_merge;
use function array_values;
use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type MutantProcessFactory = Closure():MutantProcess
 *
 * @internal
 */
final class LazyMutantEvaluationPipe implements MutantEvaluationPipe
{
    /**
     * @var list<MutantProcessFactory>
     */
    private array $factories = [];

    /**
     * @var list<MutantProcess>
     */
    private array $processes = [];

    private bool $consumed = false;

    private int $currentProcessIndex = 0;

    /**
     * @param MutantProcessFactory ...$factories
     */
    public function __construct(
        Closure ...$factories,
    ) {
        $this->factories = array_values($factories);
    }

    /**
     * @param non-empty-array<self> $pipes
     */
    public static function merge(array $pipes): self
    {
        $factoriesList = [];

        foreach ($pipes as $pipe) {
            Assert::false($pipe->consumed);

            $factoriesList[] = $pipe->factories;
        }

        return new self(
            ...array_merge(...$factoriesList),
        );
    }

    public function hasNext(): bool
    {
        return array_key_exists($this->currentProcessIndex + 1, $this->factories)
            && $this->getCurrentMutantProcessDetectionStatus() === DetectionStatus::ESCAPED;
    }

    public function createNext(): MutantProcess
    {
        Assert::true($this->hasNext());
        $this->consumed = true;

        ++$this->currentProcessIndex;
        $nextFactory = $this->factories[$this->currentProcessIndex];

        $this->processes[] = $process = $nextFactory();

        return $process;
    }

    public function getCurrent(): MutantProcess
    {
        $this->consumed = true;

        if (!array_key_exists($this->currentProcessIndex, $this->processes)) {
            $factory = $this->factories[$this->currentProcessIndex];
            $process = $factory();
            Assert::isInstanceOf($process, MutantProcess::class);
            $this->processes[] = $process;
        }

        return $this->processes[$this->currentProcessIndex];
    }

    private function getCurrentMutantProcessDetectionStatus(): DetectionStatus
    {
        return $this->getCurrent()->getMutantExecutionResult()->getDetectionStatus();
    }
}
