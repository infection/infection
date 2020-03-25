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

namespace Infection\Tests\Mutation;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutation\DetectionStatus;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationCalculatedState;
use Infection\Mutation\MutationExecutionResultFactory;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutationProcess;
use Infection\Tests\Mutator\MutatorName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class MutationExecutionResultFactoryTest extends TestCase
{
    use MutationExecutionResultAssertions;

    /**
     * @var TestFrameworkAdapter|MockObject
     */
    private $testFrameworkAdapterMock;

    /**
     * @var MutationExecutionResultFactory
     */
    private $resultFactory;

    protected function setUp(): void
    {
        $this->testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);

        $this->resultFactory = new MutationExecutionResultFactory($this->testFrameworkAdapterMock);
    }

    public function test_it_can_create_a_result_from_a_non_covered_mutation_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"'
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn($processOutput = 'Passed!')
        ;

        $this->testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF;

        $mutationProcess = new MutationProcess(
            $processMock,
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                $mutatorName = MutatorName::getName(For_::class),
                [
                    'startLine' => $originalStartingLine = 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                [],
                static function () use ($mutationDiff): MutationCalculatedState {
                    return new MutationCalculatedState(
                        '0800f',
                        '/path/to/mutation',
                        'notCovered#0',
                        $mutationDiff
                    );
                }
            )
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutationProcess),
            $processCommandLine,
            $processOutput,
            DetectionStatus::NOT_COVERED,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_create_a_result_from_a_timed_out_mutation_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"'
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn($processOutput = '')
        ;

        $this->testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'timedOut#0';

DIFF;

        $mutationProcess = new MutationProcess(
            $processMock,
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                $mutatorName = MutatorName::getName(For_::class),
                [
                    'startLine' => $originalStartingLine = 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                [
                    new TestLocation(
                        'FooTest::test_it_can_instantiate',
                        '/path/to/acme/FooTest.php',
                        0.01
                    ),
                ],
                static function () use ($mutationDiff): MutationCalculatedState {
                    return new MutationCalculatedState(
                        '0800f',
                        '/path/to/mutation',
                        'timedOut#0',
                        $mutationDiff
                    );
                }
            )
        );

        $mutationProcess->markAsTimedOut();

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutationProcess),
            $processCommandLine,
            $processOutput,
            DetectionStatus::TIMED_OUT,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_create_a_result_from_an_errored_mutation_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"'
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

        $this->testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'errored#0';

DIFF;

        $mutationProcess = new MutationProcess(
            $processMock,
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                $mutatorName = MutatorName::getName(For_::class),
                [
                    'startLine' => $originalStartingLine = 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                [
                    new TestLocation(
                        'FooTest::test_it_can_instantiate',
                        '/path/to/acme/FooTest.php',
                        0.01
                    ),
                ],
                static function () use ($mutationDiff): MutationCalculatedState {
                    return new MutationCalculatedState(
                        '0800f',
                        '/path/to/mutation',
                        'errored#0',
                        $mutationDiff
                    );
                }
            )
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutationProcess),
            $processCommandLine,
            $processOutput,
            DetectionStatus::ERROR,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_crate_a_result_from_an_escaped_mutation_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"'
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('Tests passed!')
        ;
        $processMock
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(0)
        ;

        $this->testFrameworkAdapterMock
            ->expects($this->once())
            ->method('testsPass')
            ->with('Tests passed!')
            ->willReturn(true)
        ;

        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'escaped#0';

DIFF;

        $mutationProcess = new MutationProcess(
            $processMock,
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                $mutatorName = MutatorName::getName(For_::class),
                [
                    'startLine' => $originalStartingLine = 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                [
                    new TestLocation(
                        'FooTest::test_it_can_instantiate',
                        '/path/to/acme/FooTest.php',
                        0.01
                    ),
                ],
                static function () use ($mutationDiff): MutationCalculatedState {
                    return new MutationCalculatedState(
                        '0800f',
                        '/path/to/mutation',
                        'escaped#0',
                        $mutationDiff
                    );
                }
            )
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutationProcess),
            $processCommandLine,
            'Tests passed!',
            DetectionStatus::ESCAPED,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    public function test_it_can_crate_a_result_from_a_killed_mutation_process(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn(
                $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"'
            )
        ;
        $processMock
            ->method('isTerminated')
            ->willReturn(true)
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('Tests failed!')
        ;
        $processMock
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(0)
        ;

        $this->testFrameworkAdapterMock
            ->expects($this->once())
            ->method('testsPass')
            ->with('Tests failed!')
            ->willReturn(false)
        ;

        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

DIFF;

        $mutationProcess = new MutationProcess(
            $processMock,
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                $mutatorName = MutatorName::getName(For_::class),
                [
                    'startLine' => $originalStartingLine = 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                [
                    new TestLocation(
                        'FooTest::test_it_can_instantiate',
                        '/path/to/acme/FooTest.php',
                        0.01
                    ),
                ],
                static function () use ($mutationDiff): MutationCalculatedState {
                    return new MutationCalculatedState(
                        '0800f',
                        '/path/to/mutation',
                        'killed#0',
                        $mutationDiff
                    );
                }
            )
        );

        $this->assertResultStateIs(
            $this->resultFactory->createFromProcess($mutationProcess),
            $processCommandLine,
            'Tests failed!',
            DetectionStatus::KILLED,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }
}
