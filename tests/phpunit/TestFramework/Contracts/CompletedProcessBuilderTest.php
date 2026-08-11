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

namespace Infection\Tests\TestFramework\Contracts;

use Infection\TestFramework\Contracts\CompletedProcess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompletedProcessBuilder::class)]
final class CompletedProcessBuilderTest extends TestCase
{
    public function test_it_builds_with_minimal_test_data(): void
    {
        $expected = new CompletedProcess(
            command: [],
            exitCode: 0,
            stdout: '',
            stderr: '',
        );
        $actual = CompletedProcessBuilder::withMinimalTestData()->build();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_builds_with_overridden_data_without_changing_the_original_builder(): void
    {
        $originalBuilder = CompletedProcessBuilder::withMinimalTestData();

        $expectedOverriddenCompletedProcess = new CompletedProcess(
            ['php', '--version'],
            1,
            'stdout',
            'stderr',
        );
        $actualOverriddenCompletedProcess = $originalBuilder
            ->withCommand(['php', '--version'])
            ->withExitCode(1)
            ->withStdout('stdout')
            ->withStderr('stderr')
            ->build();

        $expectedOriginalCompletedProcess = new CompletedProcess(
            command: [],
            exitCode: 0,
            stdout: '',
            stderr: '',
        );
        $actualOriginalCompletedProcess = $originalBuilder->build();

        $this->assertEquals($expectedOverriddenCompletedProcess, $actualOverriddenCompletedProcess);
        $this->assertEquals($expectedOriginalCompletedProcess, $actualOriginalCompletedProcess);
    }
}
