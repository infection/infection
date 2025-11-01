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

use Infection\Mutant\DetectionStatus;
use Infection\Mutator\Loop\For_;
use Infection\Mutator\Loop\Foreach_;
use Infection\Testing\MutatorName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutantExecutionResultBuilder::class)]
final class MutantExecutionResultBuilderTest extends TestCase
{
    use MutantExecutionResultAssertions;

    #[DataProvider('executionResultProvider')]
    public function test_it_can_create_a_mutant_execution_result(
        MutantExecutionResultBuilder $builder,
        string $expectedProcessCommandLine,
        string $expectedProcessOutput,
        DetectionStatus $expectedDetectionStatus,
        string $expectedMutantDiff,
        string $expectedMutatorName,
        string $expectedOriginalFilePath,
        int $expectedOriginalStartingLine,
    ): void {
        $result = $builder->build();

        $this->assertResultStateIs(
            $result,
            $expectedProcessCommandLine,
            $expectedProcessOutput,
            $expectedDetectionStatus,
            $expectedMutantDiff,
            $expectedMutatorName,
            $expectedOriginalFilePath,
            $expectedOriginalStartingLine,
        );
    }

    public static function executionResultProvider(): iterable
    {
        yield 'minimal execution result' => [
            MutantExecutionResultBuilder::withMinimalTestData(),
            'vendor/bin/phpunit --configuration phpunit.xml',
            '',
            DetectionStatus::KILLED_BY_TESTS,
            '--- Original
+++ Mutated
@@ @@
-$a = 1;
+$a = 2;',
            MutatorName::getName(For_::class),
            'src/Foo.php',
            10,
        ];

        yield 'complete execution result' => [
            MutantExecutionResultBuilder::withCompleteTestData(),
            'vendor/bin/phpunit --configuration phpunit.xml --filter FooTest',
            'PHPUnit 11.0.0 by Sebastian Bergmann

Time: 00:00.123, Memory: 16.00 MB

FAILURES!
Tests: 2, Assertions: 5, Failures: 1.',
            DetectionStatus::KILLED_BY_TESTS,
            '--- Original
+++ Mutated
@@ @@
-        for ($i = 0; $i < 10; $i++) {
-            echo $i;
-        }
+        // Mutated: removed for loop',
            MutatorName::getName(For_::class),
            '/path/to/src/Foo.php',
            10,
        ];

        yield 'escaped mutant' => [
            MutantExecutionResultBuilder::withMinimalTestData()
                ->withDetectionStatus(DetectionStatus::ESCAPED)
                ->withProcessOutput('OK (5 tests, 10 assertions)'),
            'vendor/bin/phpunit --configuration phpunit.xml',
            'OK (5 tests, 10 assertions)',
            DetectionStatus::ESCAPED,
            '--- Original
+++ Mutated
@@ @@
-$a = 1;
+$a = 2;',
            MutatorName::getName(For_::class),
            'src/Foo.php',
            10,
        ];

        yield 'timed out mutant' => [
            MutantExecutionResultBuilder::withMinimalTestData()
                ->withDetectionStatus(DetectionStatus::TIMED_OUT)
                ->withProcessRuntime(10.0),
            'vendor/bin/phpunit --configuration phpunit.xml',
            '',
            DetectionStatus::TIMED_OUT,
            '--- Original
+++ Mutated
@@ @@
-$a = 1;
+$a = 2;',
            MutatorName::getName(For_::class),
            'src/Foo.php',
            10,
        ];

        yield 'custom mutator' => [
            MutantExecutionResultBuilder::withMinimalTestData()
                ->withMutatorClass(Foreach_::class)
                ->withMutatorName(MutatorName::getName(Foreach_::class)),
            'vendor/bin/phpunit --configuration phpunit.xml',
            '',
            DetectionStatus::KILLED_BY_TESTS,
            '--- Original
+++ Mutated
@@ @@
-$a = 1;
+$a = 2;',
            MutatorName::getName(Foreach_::class),
            'src/Foo.php',
            10,
        ];
    }

    #[DataProvider('executionResultProvider')]
    public function test_it_can_build_from_existing_result(MutantExecutionResultBuilder $builder): void
    {
        $expected = $builder->build();

        $actual = MutantExecutionResultBuilder::from($expected)->build();

        $this->assertResultEquals($expected, $actual);
    }
}
