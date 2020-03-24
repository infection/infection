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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationCalculatedState;
use Infection\Mutator\ZeroIteration\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;

final class MutantExecutionResultTest extends TestCase
{
    use MutantExecutionResultAssertions;

    public function test_it_can_be_instantiated(): void
    {
        $processCommandLine = 'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"';
        $processOutput = 'Passed!';
        $processResultCode = DetectionStatus::ESCAPED;
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

    public function test_it_can_be_instantiated_from_a_non_covered_mutant(): void
    {
        $mutationDiff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF;

        $mutant = new Mutation(
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
                    'notCovered#0',
                    $mutationDiff
                );
            }
        );

        $this->assertResultStateIs(
            MutantExecutionResult::createFromNonCoveredMutant($mutant),
            '',
            '',
            DetectionStatus::NOT_COVERED,
            $mutationDiff,
            $mutatorName,
            $originalFilePath,
            $originalStartingLine
        );
    }

    private function assertResultStateIs(
        MutantExecutionResult $result,
        string $expectedProcessCommandLine,
        string $expectedProcessOutput,
        string $expectedDetectionStatus,
        string $expectedMutantDiff,
        string $expectedMutatorName,
        string $expectedOriginalFilePath,
        int $expectedOriginalStartingLine
    ): void {
        $this->assertSame($expectedProcessCommandLine, $result->getProcessCommandLine());
        $this->assertSame($expectedProcessOutput, $result->getProcessOutput());
        $this->assertSame($expectedDetectionStatus, $result->getDetectionStatus());
        $this->assertSame($expectedMutantDiff, $result->getMutantDiff());
        $this->assertSame($expectedMutatorName, $result->getMutatorName());
        $this->assertSame($expectedOriginalFilePath, $result->getOriginalFilePath());
        $this->assertSame($expectedOriginalStartingLine, $result->getOriginalStartingLine());
    }
}
