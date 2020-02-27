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

namespace Infection\Tests\Mutant;

use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Infection\Mutator\ZeroIteration\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\MutantProcess;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class MutantExecutionResultTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"';
        $processOutput = 'Passed!';
        $processResultCode = MutantProcess::CODE_ESCAPED;
        $mutantDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF;

        $mutatorName = MutatorName::getName(For_::class);
        $originalFilePath = 'path/to/Foo.php';
        $originalStartingLine = 10;

        $result = new MutantExecutionResult(
            $processCommandLine,
            $processOutput,
            $processResultCode,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );

        $this->assertResultStateIs(
            $result,
            $processCommandLine,
            $processOutput,
            $processResultCode,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_be_instantiated_from_a_mutant_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn($processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"')
        ;
        $processMock
            ->method('isStarted')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn($processOutput = 'Passed!')
        ;
        $processMock
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(152)
        ;

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            new Mutant(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    $mutatorName = MutatorName::getName(For_::class),
                    [
                        'startLine' => $originalStartingLine = 10,
                        'endLine' => 15,
                        'startTokenPos' => 0,
                        'endTokenPos' => 8,
                        'startFilePos' => 2,
                        'endFilePos' => 4,
                    ],
                    'Unknown',
                    MutatedNode::wrap(new Nop()),
                    0,
                    [
                        CoverageLineData::with(
                            'FooTest::test_it_can_instantiate',
                            '/path/to/acme/FooTest.php',
                            0.01
                        ),
                    ]
                ),
                $mutantDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
            ),
            $testFrameworkAdapterMock
        );

        $this->assertResultStateIs(
            MutantExecutionResult::createFromProcess($mutantProcess),
            $processCommandLine,
            $processOutput,
            MutantProcess::CODE_ERROR,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_be_instantiated_from_an_escaped_mutant_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn($processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"')
        ;
        $processMock
            ->method('isStarted')
            ->willReturn(false)
        ;
        $processMock
            ->expects($this->never())
            ->method('getOutput')
        ;
        $processMock
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(152)
        ;

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            new Mutant(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    $mutatorName = MutatorName::getName(For_::class),
                    [
                        'startLine' => $originalStartingLine = 10,
                        'endLine' => 15,
                        'startTokenPos' => 0,
                        'endTokenPos' => 8,
                        'startFilePos' => 2,
                        'endFilePos' => 4,
                    ],
                    'Unknown',
                    MutatedNode::wrap(new Nop()),
                    0,
                    [
                        CoverageLineData::with(
                            'FooTest::test_it_can_instantiate',
                            '/path/to/acme/FooTest.php',
                            0.01
                        ),
                    ]
                ),
                $mutantDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
            ),
            $testFrameworkAdapterMock
        );

        $this->assertResultStateIs(
            MutantExecutionResult::createFromProcess($mutantProcess),
            $processCommandLine,
            '',
            MutantProcess::CODE_ERROR,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    private function assertResultStateIs(
        MutantExecutionResult $result,
        string $expectedProcessCommandLine,
        string $expectedProcessOutput,
        int $expectedProcessResultCode,
        string $expectedMutantDiff,
        string $expectedMutatorName,
        string $expectedOriginalFilePath,
        int $expectedOriginalStartingLine
    ): void {
        $this->assertSame($expectedProcessCommandLine, $result->getProcessCommandLine());
        $this->assertSame($expectedProcessOutput, $result->getProcessOutput());
        $this->assertSame($expectedProcessResultCode, $result->getProcessResultCode());
        $this->assertSame($expectedMutantDiff, $result->getMutantDiff());
        $this->assertSame($expectedMutatorName, $result->getMutatorName());
        $this->assertSame($expectedOriginalFilePath, $result->getOriginalFilePath());
        $this->assertSame($expectedOriginalStartingLine, $result->getOriginalStartingLine());
    }
}
