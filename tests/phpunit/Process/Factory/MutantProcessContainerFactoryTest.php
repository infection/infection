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
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\ForkedProcess;
use Infection\Process\ForkWorkerPool;
use Infection\Testing\MutatorName;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Mutant\MutantBuilder;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(MutantProcessContainerFactory::class)]
final class MutantProcessContainerFactoryTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/../../Fixtures/ForkWorker';

    #[DataProvider('timeoutDataProvider')]
    public function test_it_creates_a_process_with_timeout(float $expectedProcessTimeout, float $testLocationExecutionTime, int $processFactoryTimeout): void
    {
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutantFilePath($mutantFilePath = '/path/to/mutant')
            ->withMutation(new Mutation(
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
            ))
            ->withMutatedCode('killed#0')
            ->withDiff($mutantDiff = <<<'DIFF'
                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                DIFF)
            ->withPrettyPrintedOriginalCode('<?php $a = 1;')
            ->build()
        ;

        $testFrameworkExtraOptions = '--verbose';

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->method('getMutantCommandLine')
            ->with(
                $tests,
                $mutantFilePath,
                $this->isString(),
                $originalFilePath,
                $testFrameworkExtraOptions,
            )
            ->willReturn(['/usr/bin/php', 'bin/phpunit', '--filter', '/path/to/acme/FooTest.php'])
        ;

        $eventDispatcher = new EventDispatcherCollector();

        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withDryRun(false)
            ->build()
        ;

        $factory = new MutantProcessContainerFactory(
            $processFactoryTimeout,
            [],
            $configuration,
            new ForkWorkerPool(),
        );

        $mutantProcess = $factory->create($testFrameworkAdapterMock, $mutant, $testFrameworkExtraOptions);

        $process = $mutantProcess->getCurrent()->getProcess();

        $this->assertContains($process->getCommandLine(), [
            "'/usr/bin/php' 'bin/phpunit' '--filter' '/path/to/acme/FooTest.php'",
            // Windows variants
            '"/usr/bin/php" "bin/phpunit" --filter "/path/to/acme/FooTest.php"',
            '/usr/bin/php bin/phpunit --filter /path/to/acme/FooTest.php',
        ]);

        $this->assertSame($expectedProcessTimeout, $process->getTimeout());
        $this->assertSame(['SHELL_VERBOSITY' => '0'], $process->getEnv());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getCurrent()->getMutant());
        $this->assertFalse($mutantProcess->getCurrent()->isTimedOut());

        $this->assertSame([], $eventDispatcher->getEvents());
    }

    public static function timeoutDataProvider(): iterable
    {
        yield 'minimum timeout on a fast test' => [5.05, 0.01, 90];

        yield 'allows 5x more time than test-execution' => [30.0, 5.0, 90];

        yield 'slow tests do not get more time than factory-timeout' => [40.0, 10.0, 40];
    }

    #[RequiresPhpExtension('pcntl')]
    #[RequiresPhpExtension('posix')]
    public function test_it_runs_a_php_script_in_a_fork_of_a_worker(): void
    {
        $process = $this->createProcess([self::FIXTURES . '/report.php']);

        $this->assertInstanceOf(ForkedProcess::class, $process);
        $this->assertFalse($process->isStarted());
    }

    #[WithEnvironmentVariable('INFECTION_FORK', '0')]
    #[RequiresPhpExtension('pcntl')]
    #[RequiresPhpExtension('posix')]
    public function test_it_starts_a_new_process_for_a_php_script_when_the_fork_is_disabled(): void
    {
        $this->assertNotInstanceOf(ForkedProcess::class, $this->createProcess([self::FIXTURES . '/report.php']));
    }

    public function test_it_starts_a_new_process_for_a_command_that_is_not_a_php_script(): void
    {
        $this->assertNotInstanceOf(ForkedProcess::class, $this->createProcess(['/usr/bin/php', 'bin/phpunit']));
    }

    /**
     * @param array<string> $command
     */
    private function createProcess(array $command): Process
    {
        $testFrameworkAdapter = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapter
            ->method('getMutantCommandLine')
            ->willReturn($command)
        ;

        $factory = new MutantProcessContainerFactory(
            10.0,
            [],
            ConfigurationBuilder::withMinimalTestData()
                ->withDryRun(false)
                ->build(),
            new ForkWorkerPool(),
        );

        return $factory->create(
            $testFrameworkAdapter,
            MutantBuilder::withMinimalTestData()->build(),
        )->getCurrent()->getProcess();
    }
}
