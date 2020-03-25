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

namespace Infection\Tests\Process\Builder;

use function current;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\MutantProcessWasFinished;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\ZeroIteration\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\Builder\MutantProcessFactory;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Mutator\MutatorName;
use const PHP_OS_FAMILY;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;

final class MutantProcessFactoryTest extends TestCase
{
    public function test_it_creates_a_process_with_timeout(): void
    {
        $mutant = new Mutant(
            $mutantFilePath = '/path/to/mutant',
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                [],
                MutatorName::getName(For_::class),
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
                $tests = [
                    new TestLocation(
                        'FooTest::test_it_can_instantiate',
                        '/path/to/acme/FooTest.php',
                        0.01
                    ),
                ]
            ),
            'killed#0',
            $mutantDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'killed#0';

DIFF
        );

        $timeout = 100;
        $testFrameworkExtraOptions = '--verbose';

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->method('getMutantCommandLine')
            ->with(
                $tests,
                $mutantFilePath,
                $this->isType('string'),
                $originalFilePath,
                $testFrameworkExtraOptions
            )
            ->willReturn(['/usr/bin/php', 'bin/phpunit', '--filter', '/path/to/acme/FooTest.php'])
        ;

        $eventDispatcher = new EventDispatcherCollector();

        $executionResultMock = $this->createMock(MutantExecutionResult::class);
        $executionResultMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $resultFactoryMock = $this->createMock(MutantExecutionResultFactory::class);
        $resultFactoryMock
            ->method('createFromProcess')
            ->willReturn($executionResultMock)
        ;

        $factory = new MutantProcessFactory(
            $testFrameworkAdapterMock,
            100,
            $eventDispatcher,
            $resultFactoryMock
        );

        $mutantProcess = $factory->createProcessForMutant($mutant, $testFrameworkExtraOptions);

        $process = $mutantProcess->getProcess();

        $this->assertSame(
            PHP_OS_FAMILY === 'Windows'
                ? '"/usr/bin/php" "bin/phpunit" --filter "/path/to/acme/FooTest.php"'
                : "'/usr/bin/php' 'bin/phpunit' '--filter' '/path/to/acme/FooTest.php'",
            $process->getCommandLine()
        );
        $this->assertSame(100., $process->getTimeout());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getMutant());
        $this->assertFalse($mutantProcess->isTimedOut());

        $this->assertSame([], $eventDispatcher->getEvents());

        $mutantProcess->terminateProcess();

        $eventsAfterCallbackCall = $eventDispatcher->getEvents();

        $this->assertCount(1, $eventsAfterCallbackCall);

        $event = current($eventsAfterCallbackCall);

        $this->assertInstanceOf(MutantProcessWasFinished::class, $event);
        $this->assertSame($executionResultMock, $event->getExecutionResult());
    }
}
