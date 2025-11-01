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

namespace Infection\Tests\StaticAnalysis\PHPStan\Process;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\StaticAnalysis\PHPStan\Process\PHPStanMutantProcessFactory;
use Infection\TestFramework\CommandLineBuilder;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutant\MutantBuilder;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(PHPStanMutantProcessFactory::class)]
final class PHPStanMutantProcessFactoryTest extends TestCase
{
    public function test_it_creates_a_process_with_timeout(): void
    {
        $mutant = MutantBuilder::materialize(
            $mutantFilePath = '/path/to/mutant',
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                [],
                For_::class,
                MutatorName::getName(For_::class),
                [
                    'startLine' => 10,
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
                [],
                '',
            ),
            'killed#0',
            <<<'DIFF'
                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                DIFF,
            '<?php $a = 1;',
        );

        $phpStanMutantExecutionResultFactory = $this->createMock(PHPStanMutantExecutionResultFactory::class);
        $commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                "--tmp-file=$mutantFilePath",
                "--instead-of=$originalFilePath",
                '--configuration=/tmp/phpstan.83a21d5b6b2410a132e35273b02a3424.infection.neon',
                '--error-format=json',
                '--no-progress',
                '-vv',
                '--memory-limit=-1',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan'])
        ;

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                '/tmp/phpstan.83a21d5b6b2410a132e35273b02a3424.infection.neon',
                <<<NEON
                        includes:
                            - /path/to/phpstan-config-folder
                        parameters:
                            reportUnmatchedIgnoredErrors: false
                            parallel:
                                maximumNumberOfProcesses: 1
                    NEON,
            );

        $factory = new PHPStanMutantProcessFactory(
            $filesystem,
            $phpStanMutantExecutionResultFactory,
            '/path/to/phpstan-config-folder',
            '/path/to/phpstan',
            $commandLineBuilder,
            100.0,
            '/tmp',
            ['--memory-limit=-1'],
        );

        $mutantProcess = $factory->create($mutant);

        $process = $mutantProcess->getProcess();

        $this->assertSame(100.0, $process->getTimeout());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getMutant());
        $this->assertFalse($mutantProcess->isTimedOut());
    }

    public function test_it_creates_a_process_with_multiple_options(): void
    {
        $mutant = MutantBuilder::materialize(
            $mutantFilePath = '/path/to/mutant',
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                [],
                For_::class,
                MutatorName::getName(For_::class),
                [
                    'startLine' => 10,
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
                [],
                '',
            ),
            'killed#0',
            <<<'DIFF'
                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                DIFF,
            '<?php $a = 1;',
        );

        $phpStanMutantExecutionResultFactory = $this->createMock(PHPStanMutantExecutionResultFactory::class);
        $commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                "--tmp-file=$mutantFilePath",
                "--instead-of=$originalFilePath",
                '--configuration=/tmp/phpstan.83a21d5b6b2410a132e35273b02a3424.infection.neon',
                '--error-format=json',
                '--no-progress',
                '-vv',
                '--memory-limit=-1',
                '--level=max',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan'])
        ;

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                '/tmp/phpstan.83a21d5b6b2410a132e35273b02a3424.infection.neon',
                <<<NEON
                        includes:
                            - /path/to/phpstan-config-folder
                        parameters:
                            reportUnmatchedIgnoredErrors: false
                            parallel:
                                maximumNumberOfProcesses: 1
                    NEON,
            );

        $factory = new PHPStanMutantProcessFactory(
            $filesystem,
            $phpStanMutantExecutionResultFactory,
            '/path/to/phpstan-config-folder',
            '/path/to/phpstan',
            $commandLineBuilder,
            100.0,
            '/tmp',
            ['--memory-limit=-1', '--level=max'],
        );

        $mutantProcess = $factory->create($mutant);

        $process = $mutantProcess->getProcess();

        $this->assertSame(100.0, $process->getTimeout());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getMutant());
        $this->assertFalse($mutantProcess->isTimedOut());
    }

    public function test_it_creates_a_process_without_options(): void
    {
        $mutant = MutantBuilder::materialize(
            $mutantFilePath = '/path/to/mutant',
            new Mutation(
                $originalFilePath = 'path/to/Foo.php',
                [],
                For_::class,
                MutatorName::getName(For_::class),
                [
                    'startLine' => 10,
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
                [],
                '',
            ),
            'killed#0',
            <<<'DIFF'
                --- Original
                +++ New
                @@ @@

                - echo 'original';
                + echo 'killed#0';

                DIFF,
            '<?php $a = 1;',
        );

        $phpStanMutantExecutionResultFactory = $this->createMock(PHPStanMutantExecutionResultFactory::class);
        $commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                "--tmp-file=$mutantFilePath",
                "--instead-of=$originalFilePath",
                '--configuration=/tmp/phpstan.83a21d5b6b2410a132e35273b02a3424.infection.neon',
                '--error-format=json',
                '--no-progress',
                '-vv',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan'])
        ;

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('dumpFile');

        $factory = new PHPStanMutantProcessFactory(
            $filesystem,
            $phpStanMutantExecutionResultFactory,
            '/path/to/phpstan-config-folder',
            '/path/to/phpstan',
            $commandLineBuilder,
            100.0,
            '/tmp',
            [],
        );

        $mutantProcess = $factory->create($mutant);

        $process = $mutantProcess->getProcess();

        $this->assertSame(100.0, $process->getTimeout());
        $this->assertFalse($process->isStarted());

        $this->assertSame($mutant, $mutantProcess->getMutant());
        $this->assertFalse($mutantProcess->isTimedOut());
    }
}
