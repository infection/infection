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

namespace Infection\Tests\Testing\TestFramework\Debug;

use Infection\Process\DryRunProcess;
use Infection\TestFramework\Common\Process\DryRunAwareProcessFactory;
use Infection\Testing\TestFramework\Debug\DebugCommandLine;
use Infection\Testing\TestFramework\Debug\DebugStaticAnalysisMutantProcessFactory;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[CoversClass(DebugStaticAnalysisMutantProcessFactory::class)]
final class DebugStaticAnalysisMutantProcessFactoryTest extends TestCase
{
    #[DataProvider('processModeProvider')]
    public function test_it_creates_a_process_with_normal_shell_verbosity(bool $dryRun, string $expectedProcessClass): void
    {
        $factory = new DebugStaticAnalysisMutantProcessFactory(
            new DryRunAwareProcessFactory($dryRun),
            '/debug.php',
            '/debug.jsonl',
            10.,
            new DebugCommandLine(new PhpExecutableFinder()),
        );

        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $factory->create($mutant)->getProcess();

        $processClass = $process::class;
        $commandLine = $process->getCommandLine();
        $mutationHash = $mutant->getMutation()->getHash();
        $this->assertSame($expectedProcessClass, $processClass);
        $this->assertSame(['SHELL_VERBOSITY' => '0'], $process->getEnv());
        $this->assertSame(10.0, $process->getTimeout());
        $this->assertStringContainsString('static-analysis-mutant', $commandLine);
        $this->assertStringContainsString('--log', $commandLine);
        $this->assertStringContainsString('/debug.jsonl', $commandLine);
        $this->assertStringContainsString('--mutationHash', $commandLine);
        $this->assertStringContainsString($mutationHash, $commandLine);
    }

    public static function processModeProvider(): iterable
    {
        yield 'ordinary process' => [false, Process::class];

        yield 'dry-run process' => [true, DryRunProcess::class];
    }
}
