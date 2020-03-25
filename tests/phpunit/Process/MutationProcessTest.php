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

namespace Infection\Tests\Process;

use Infection\Mutation\Mutation;
use Infection\Process\MutationProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class MutationProcessTest extends TestCase
{
    /**
     * @var MockObject|Process
     */
    private $processMock;

    /**
     * @var MockObject|Mutation
     */
    private $mutationMock;

    /**
     * @var MutationProcess
     */
    private $mutationProcess;

    protected function setUp(): void
    {
        $this->processMock = $this->createMock(Process::class);
        $this->mutationMock = $this->createMock(Mutation::class);

        $this->mutationProcess = new MutationProcess($this->processMock, $this->mutationMock);
    }

    public function test_it_exposes_its_state(): void
    {
        $this->assertMutationProcessStateIs(
            $this->mutationProcess,
            $this->processMock,
            $this->mutationMock,
            false
        );
    }

    public function test_it_can_be_marked_as_timed_out(): void
    {
        $this->mutationProcess->markAsTimedOut();

        $this->assertMutationProcessStateIs(
            $this->mutationProcess,
            $this->processMock,
            $this->mutationMock,
            true
        );
    }

    public function test_it_can_have_a_callback_registered_and_executed(): void
    {
        $called = false;

        $this->mutationProcess->registerTerminateProcessClosure(
            static function () use (&$called): void {
                $called = true;
            }
        );

        $this->assertFalse($called);

        $this->mutationProcess->terminateProcess();

        $this->assertTrue($called);
    }

    private function assertMutationProcessStateIs(
        MutationProcess $mutationProcess,
        Process $expectedProcess,
        Mutation $expectedMutation,
        bool $expectedTimedOut
    ): void {
        $this->assertSame($expectedProcess, $mutationProcess->getProcess());
        $this->assertSame($expectedMutation, $mutationProcess->getMutation());
        $this->assertSame($expectedTimedOut, $mutationProcess->isTimedOut());
    }
}
