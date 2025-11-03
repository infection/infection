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

namespace Infection\Tests\Process\Factory;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Testing\MutatorName;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutantProcessContainerFactory::class)]
final class MutantProcessContainerFactoryTest extends TestCase
{
    #[DataProvider('timeoutDataProvider')]
    public function test_it_creates_a_process_with_timeout(float $expectedProcessTimeout, float $testLocationExecutionTime, int $processFactoryTimeout): void
    {
        $mutant = MutantBuilder::materialize(
            $mutantFilePath = '/path/to/mutant',
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                [],
                For_::class,
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
                        $testLocationExecutionTime,
                    ),
                ],
                [],
                '',
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
        );

        $testFrameworkExtraOptions = '--verbose';

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->method('getMutantCommandLine')
            ->with(
                $tests,
                $mutantFilePath,
                $this->isType('string'),
                $originalFilePath,
                $testFrameworkExtraOptions,
            )
            ->willReturn(['/usr/bin/php', 'bin/phpunit', '--filter', '/path/to/acme/FooTest.php'])
        ;

        $eventDispatcher = new EventDispatcherCollector();

        $executionResult = MutantExecutionResultBuilder::withMinimalTestData()->build();

        $resultFactoryMock = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);
        $resultFactoryMock
            ->method('createFromProcess')
            ->willReturn($executionResult)
        ;

        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withDryRun(false)
            ->build();

        $factory = new MutantProcessContainerFactory(
            $testFrameworkAdapterMock,
            $processFactoryTimeout,
            $resultFactoryMock,
            [],
            $configuration,
        );

        $mutantProcess = $factory->create($mutant, $testFrameworkExtraOptions);

        $process = $mutantProcess->getCurrent()->getProcess();

        $this->assertContains($process->getCommandLine(), [
            "'/usr/bin/php' 'bin/phpunit' '--filter' '/path/to/acme/FooTest.php'",
            // Windows variants
            '"/usr/bin/php" "bin/phpunit" --filter "/path/to/acme/FooTest.php"',
            '/usr/bin/php bin/phpunit --filter /path/to/acme/FooTest.php',
        ]);

        $this->assertSame($expectedProcessTimeout, $process->getTimeout());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getCurrent()->getMutant());
        $this->assertFalse($mutantProcess->getCurrent()->isTimedOut());

        $this->assertSame([], $eventDispatcher->getEvents());
    }

    public static function timeoutDataProvider(): iterable
    {
        return [
            'minimum timeout on a fast test' => [5.05, 0.01, 90],
            'allows 5x more time than test-execution' => [30.0, 5.0, 90],
            'slow tests do not get more time than factory-timeout' => [40.0, 10.0, 40],
        ];
    }
}
