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

namespace Infection\Tests\TestFramework\PhpUnit\Mutant;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationAttemptResultFactory;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\Process\DryRunProcessFactory;
use Infection\TestFramework\Common\Process\ProcessFactory;
use Infection\TestFramework\Common\Process\SymfonyProcessFactory;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\MemoryLimiter;
use Infection\TestFramework\PhpUnit\MemoryLimiterEnvironment;
use Infection\TestFramework\PhpUnit\Mutant\PHPUnitMutantProcessFactory;
use Infection\TestFramework\Tracing\TestRunOrderResolver;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[CoversClass(PHPUnitMutantProcessFactory::class)]
final class PHPUnitMutantProcessFactoryTest extends TestCase
{
    public function test_it_creates_the_mutant_process_with_the_prepared_command(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $this->createStub(Process::class);
        $processFactory = $this->createMock(ProcessFactory::class);
        $processFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    '/usr/bin/php',
                    '/path/to/phpunit',
                    '--configuration',
                    '/tmp/phpunitConfiguration.' . $mutant->getMutation()->getHash() . '.infection.xml',
                    '--verbose',
                ],
                null,
                [],
                null,
                5.0,
            )
            ->willReturn($process)
        ;
        $factory = $this->createFactory($processFactory);
        $attempt = $factory->create($mutant, '12.5.0', '--verbose');

        $this->assertSame($process, $attempt->getProcess());
        $this->assertSame($mutant, $attempt->getMutant());
    }

    #[DataProvider('timeoutDataProvider')]
    public function test_it_calculates_the_process_timeout(float $expected, float $nominalTime, float $limit): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withTests([new TestLocation('FooTest::test_it_works', '/project/tests/FooTest.php', $nominalTime)])
            ->build()
        ;
        $mutant = MutantBuilder::withMinimalTestData()->withMutation($mutation)->build();
        $factory = $this->createFactory(new SymfonyProcessFactory(), timeout: $limit);
        $attempt = $factory->create($mutant, '12.5.0', '');

        $this->assertSame($expected, $attempt->getProcess()->getTimeout());
    }

    public static function timeoutDataProvider(): iterable
    {
        yield 'bootstrap allowance' => [5.05, 0.01, 90.0];

        yield 'scales with nominal test time' => [30.0, 5.0, 90.0];

        yield 'configured limit' => [40.0, 10.0, 40.0];
    }

    public function test_it_uses_the_injected_attempt_result_factory(): void
    {
        $observation = new EvaluationAttemptResult('PHPUnit', DetectionStatus::ESCAPED, 'passed', null);
        $resultFactory = $this->createMock(EvaluationAttemptResultFactory::class);
        $resultFactory->expects($this->once())->method('createFromProcess')->willReturn($observation);
        $factory = $this->createFactory(new DryRunProcessFactory(), $resultFactory);
        $attempt = $factory->create(MutantBuilder::withMinimalTestData()->build(), '12.5.0', '');
        $attempt->markAsFinished();

        $this->assertSame($observation, $attempt->getEvaluationAttemptResult());
    }

    private function createFactory(
        ProcessFactory $processFactory,
        ?EvaluationAttemptResultFactory $resultFactory = null,
        float $timeout = 90.0,
    ): PHPUnitMutantProcessFactory {
        $fileSystem = $this->createStub(Filesystem::class);
        $phpExecutableFinder = $this->createStub(PhpExecutableFinder::class);
        $phpExecutableFinder->method('find')->willReturn('/usr/bin/php');

        return new PHPUnitMutantProcessFactory(
            $processFactory,
            $resultFactory ?? $this->createStub(EvaluationAttemptResultFactory::class),
            $timeout,
            '/path/to/phpunit',
            new CommandLineBuilder($phpExecutableFinder),
            new MutationConfigBuilder(
                '/tmp',
                '<phpunit/>',
                new XmlConfigurationManipulator(new PathReplacer($fileSystem, '/project'), '/project'),
                '/project',
                new TestRunOrderResolver(),
                $fileSystem,
            ),
            new ArgumentsAndOptionsBuilder(false, [], null, false),
            new MemoryLimiter($this->createStub(MemoryLimiterEnvironment::class)),
        );
    }
}
