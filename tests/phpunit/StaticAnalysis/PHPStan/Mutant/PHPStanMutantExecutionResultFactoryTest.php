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

namespace Infection\Tests\StaticAnalysis\PHPStan\Mutant;

use Generator;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutant\DetectionStatus;
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\MutantProcess;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultAssertions;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(PHPStanMutantExecutionResultFactory::class)]
final class PHPStanMutantExecutionResultFactoryTest extends TestCase
{
    use MutantExecutionResultAssertions;

    private PHPStanMutantExecutionResultFactory $resultFactory;

    protected function setUp(): void
    {
        $this->resultFactory = new PHPStanMutantExecutionResultFactory();
    }

    public function test_it_can_create_a_result_from_a_time_out_mutant_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpstan --tmp-file="Source.h4sz.php" --instead-of="Source.php"',
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn($processOutput = 'OK')
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            MutantBuilder::build(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    For_::class,
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
                    [],
                ),
                'notCovered#0',
                $mutantDiff = <<<'DIFF'
                    --- Original
                    +++ New
                    @@ @@

                    - echo 'original';
                    + echo 'notCovered#0';

                    DIFF,
                '<?php $a = 1;',
            ),
            $this->resultFactory,
        );

        $mutantProcess->markAsTimedOut();

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutantProcess),
            $processCommandLine,
            $processOutput,
            DetectionStatus::TIMED_OUT,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine,
        );
    }

    public function test_it_can_create_a_result_from_an_errored_mutant_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpstan --tmp-file="Source.h4sz.php" --instead-of="Source.php"',
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn($processOutput = 'Fatal Error')
        ;
        $processMock
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(152)
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            MutantBuilder::build(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    For_::class,
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
                        new TestLocation(
                            'FooTest::test_it_can_instantiate',
                            '/path/to/acme/FooTest.php',
                            0.01,
                        ),
                    ],
                ),
                'errored#0',
                $mutantDiff = <<<'DIFF'
                    --- Original
                    +++ New
                    @@ @@

                    - echo 'original';
                    + echo 'errored#0';

                    DIFF,
                '<?php $a = 1;',
            ),
            $this->resultFactory,
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutantProcess),
            $processCommandLine,
            $processOutput,
            DetectionStatus::ERROR,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine,
        );
    }

    public function test_it_can_crate_a_result_from_an_escaped_mutant_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpstan --tmp-file="Source.h4sz.php" --instead-of="Source.php"',
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('OK')
        ;
        $processMock
            ->expects($this->exactly(2))
            ->method('getExitCode')
            ->willReturn(0)
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            MutantBuilder::build(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    For_::class,
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
                        new TestLocation(
                            'FooTest::test_it_can_instantiate',
                            '/path/to/acme/FooTest.php',
                            0.01,
                        ),
                    ],
                ),
                'escaped#0',
                $mutantDiff = <<<'DIFF'
                    --- Original
                    +++ New
                    @@ @@

                    - echo 'original';
                    + echo 'escaped#0';

                    DIFF,
                '<?php $a = 1;',
            ),
            $this->resultFactory,
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutantProcess),
            $processCommandLine,
            'OK',
            DetectionStatus::ESCAPED,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine,
        );
    }

    #[DataProvider('errorCodes')]
    public function test_it_can_crate_a_result_from_a_killed_mutant_process(int $errorCode): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpstan --tmp-file="Source.h4sz.php" --instead-of="Source.php"',
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('failed')
        ;
        $processMock
            ->expects($this->exactly(2))
            ->method('getExitCode')
            ->willReturn($errorCode)
        ;

        $mutantProcess = new MutantProcess(
            $processMock,
            MutantBuilder::build(
                '/path/to/mutant',
                new Mutation(
                    $originalFilePath = 'path/to/Foo.php',
                    [],
                    For_::class,
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
                        new TestLocation(
                            'FooTest::test_it_can_instantiate',
                            '/path/to/acme/FooTest.php',
                            0.01,
                        ),
                    ],
                ),
                'killed#0',
                $mutantDiff = <<<'DIFF'
                    --- Original
                    +++ New
                    @@ @@

                    - echo 'original';
                    + echo 'killed#0';

                    DIFF,
                '<?php $a = 1;',
            ),
            $this->resultFactory,
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutantProcess),
            $processCommandLine,
            'failed',
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
            $mutantDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine,
        );
    }

    public static function errorCodes(): Generator
    {
        yield 'standard error code' => [1];

        yield 'minimum error code' => [100];
    }
}
