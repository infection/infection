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

namespace Infection\Tests\Process\Runner;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\DryProcessRunner;
use Infection\TestFramework\Mago\Mutant\MagoEvaluationAttemptResultFactory;
use Infection\TestFramework\Mago\Mutant\MagoMutantDetectionStatusResolver;
use Infection\Tests\Mutant\MutantBuilder;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(DryProcessRunner::class)]
final class DryProcessRunnerTest extends TestCase
{
    public function test_it_can_iterate_over_the_processes(): void
    {
        $called = false;

        $processes = (static function () use (&$called): iterable {
            yield MutantProcessContainer::from(
                MutantBuilder::withMinimalTestData()->build(),
                static fn (): EvaluationAttemptResult => new EvaluationAttemptResult('Direct', DetectionStatus::ESCAPED, '', null),
            );

            $called = true;

            yield MutantProcessContainer::from(
                MutantBuilder::withMinimalTestData()->build(),
                static fn (): EvaluationAttemptResult => new EvaluationAttemptResult('Direct', DetectionStatus::ESCAPED, '', null),
            );
        })();

        foreach ((new DryProcessRunner())->run($processes) as $item) {
            // just trigger a generator
        }

        $this->assertTrue($called);
    }

    public function test_it_finalizes_mixed_evaluations_without_starting_processes(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $firstProcess = new Process(['must-not-run']);
        $secondProcess = new Process(['must-not-run-either']);
        $factory = new MagoEvaluationAttemptResultFactory(new MagoMutantDetectionStatusResolver());
        $first = new MutantProcess($firstProcess, $mutant, $factory);
        $second = new MutantProcess($secondProcess, $mutant, $factory);
        $direct = new EvaluationAttemptResult('Direct', DetectionStatus::ESCAPED, 'direct output', null);
        $pipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): EvaluationAttemptResult => $direct)
            ->append(static fn (): MutantProcess => $second)
        ;

        $completed = iterator_to_array((new DryProcessRunner())->run([$pipe]), false);
        $result = $completed[0]->getMutantExecutionResult();
        $history = $result->getEvaluationHistory();

        $this->assertSame([$pipe], $completed);
        $this->assertFalse($firstProcess->isStarted());
        $this->assertFalse($secondProcess->isStarted());
        $this->assertSame(DetectionStatus::ESCAPED, $result->getDetectionStatus());
        $this->assertCount(3, $history);
        $this->assertSame($direct, $history[1]);
        $firstDetails = $history[0]->process;
        $secondDetails = $history[2]->process;
        $this->assertNotNull($firstDetails);
        $this->assertNotNull($secondDetails);
        $this->assertSame($firstProcess->getCommandLine(), $firstDetails->commandLine);
        $this->assertSame($secondProcess->getCommandLine(), $secondDetails->commandLine);
        $this->assertSame(0.0, $firstDetails->runtime);
        $this->assertSame(0.0, $secondDetails->runtime);
    }
}
