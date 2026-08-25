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

namespace Infection\Tests\TestFramework\Common;

use Infection\TestFramework\Common\CombinedMutantEvaluationPipe;
use Infection\TestFramework\Common\MutantProcessContainer;
use Infection\TestFramework\Contracts\DetectionStatus;
use Infection\TestFramework\Contracts\MutantProcess;
use Infection\TestFramework\Contracts\MutantProcessResult;
use Infection\Tests\Mutant\MutantBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedMutantEvaluationPipe::class)]
final class CombinedMutantEvaluationPipeTest extends TestCase
{
    public function test_it_evaluates_the_merged_pipes_in_order(): void
    {
        $firstProcess = $this->createMock(MutantProcess::class);
        $secondProcess = $this->createStub(MutantProcess::class);

        $firstProcess
            ->expects($this->once())
            ->method('getResult')
            ->willReturn(new MutantProcessResult('', '', '', 0., 0., DetectionStatus::ESCAPED))
        ;

        $mutant = MutantBuilder::withMinimalTestData()->build();

        $pipe = CombinedMutantEvaluationPipe::from(
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $firstProcess),
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $secondProcess),
        );

        $this->assertTrue($pipe->isCold());
        $this->assertSame($firstProcess, $pipe->getCurrent());
        $this->assertFalse($pipe->isCold());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($secondProcess, $pipe->createNext());
    }

    public function test_it_cannot_merge_a_pipe_after_its_evaluation_has_started(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $startedPipe = MutantProcessContainer::from(
            $mutant,
            fn (): MutantProcess => $this->createStub(MutantProcess::class),
        );
        $startedPipe->getCurrent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge a mutant evaluation pipe after evaluation has started.');

        CombinedMutantEvaluationPipe::from(
            $startedPipe,
            MutantProcessContainer::from($mutant, fn (): MutantProcess => $this->createStub(MutantProcess::class)),
        );
    }
}
