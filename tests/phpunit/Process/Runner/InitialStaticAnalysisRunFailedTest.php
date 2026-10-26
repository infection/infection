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

use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\Throwable\InitialTestsFailed as InitialTestsFailedContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialStaticAnalysisRunFailed::class)]
final class InitialStaticAnalysisRunFailedTest extends TestCase
{
    #[DataProvider('processOutputProvider')]
    public function test_it_creates_a_failure_from_a_process(
        string $stdout,
        string $stderr,
        string $expectedOutput,
    ): void {
        $process = new CompletedProcess(
            ['phpstan', 'analyse'],
            3,
            $stdout,
            $stderr,
        );

        $exception = InitialStaticAnalysisRunFailed::fromCompletedProcessAndAdapter(
            $process,
            'PHPStan',
        );

        $this->assertInstanceOf(
            InitialTestsFailedContract::class,
            $exception,
        );
        $this->assertSame(
            <<<'MESSAGE'
                Project static analysis must be in a passing state before running Infection.
                PHPStan reported an exit code of 3.
                Refer to the PHPStan's output below:
                MESSAGE
            . $expectedOutput,
            $exception->getMessage(),
        );
    }

    public static function processOutputProvider(): iterable
    {
        yield 'both output streams' => [
            'output string',
            'error string',
            "\nSTDOUT:\noutput string\nSTDERR:\nerror string",
        ];

        yield 'standard output only' => [
            'output string',
            '',
            "\nSTDOUT:\noutput string",
        ];

        yield 'standard error only' => [
            '',
            'error string',
            "\nSTDERR:\nerror string",
        ];

        yield 'no output' => [
            '',
            '',
            '',
        ];
    }
}
