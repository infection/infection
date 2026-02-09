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
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Testing\MutatorName;
use function is_string;
use Later\Interfaces\Deferred;
use function Later\now;

final class MutantExecutionResultBuilder
{
    /**
     * @param Deferred<string> $mutantDiff
     * @param Deferred<string> $originalCode
     * @param Deferred<string> $mutatedCode
     * @param TestLocation[] $tests
     */
    private function __construct(
        private string $processCommandLine,
        private string $processOutput,
        private DetectionStatus $detectionStatus,
        private Deferred $mutantDiff,
        private string $mutantHash,
        private string $mutatorClass,
        private string $mutatorName,
        private string $originalFilePath,
        private int $originalStartingLine,
        private int $originalEndingLine,
        private Deferred $originalCode,
        private Deferred $mutatedCode,
        private array $tests,
        private float $processRuntime,
    ) {
    }

    public static function from(MutantExecutionResult $result): self
    {
        return new self(
            $result->getProcessCommandLine(),
            $result->getProcessOutput(),
            $result->getDetectionStatus(),
            now($result->getMutantDiff()),
            $result->getMutantHash(),
            $result->getMutatorClass(),
            $result->getMutatorName(),
            $result->getOriginalFilePath(),
            $result->getOriginalStartingLine(),
            $result->getOriginalEndingLine(),
            now($result->getOriginalCode()),
            now($result->getMutatedCode()),
            $result->getTests(),
            $result->getProcessRuntime(),
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            processCommandLine: 'vendor/bin/phpunit --configuration phpunit.xml',
            processOutput: '',
            detectionStatus: DetectionStatus::KILLED_BY_TESTS,
            mutantDiff: now(
                <<<'PHP_DIFF'
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                    PHP_DIFF,
            ),
            mutantHash: 'abc123def456',
            mutatorClass: For_::class,
            mutatorName: MutatorName::getName(For_::class),
            originalFilePath: 'src/Foo.php',
            originalStartingLine: 10,
            originalEndingLine: 15,
            originalCode: now(
                <<<'PHP'
                    <?php $a = 1;
                    PHP,
            ),
            mutatedCode: now(
                <<<'PHP'
                    <?php $a = 2;
                    PHP,
            ),
            tests: [],
            processRuntime: 0.123,
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            processCommandLine: 'vendor/bin/phpunit --configuration phpunit.xml --filter FooTest',
            processOutput: <<<'STDOUT'
                PHPUnit 11.0.0 by Sebastian Bergmann

                Time: 00:00.123, Memory: 16.00 MB

                FAILURES!
                Tests: 2, Assertions: 5, Failures: 1.
                STDOUT,
            detectionStatus: DetectionStatus::KILLED_BY_TESTS,
            mutantDiff: now(
                <<<'PHP_DIFF'
                    --- Original
                    +++ Mutated
                    @@ @@
                    -        for ($i = 0; $i < 10; $i++) {
                    -            echo $i;
                    -        }
                    +        // Mutated: removed for loop
                    PHP_DIFF,
            ),
            mutantHash: 'abc123def456789',
            mutatorClass: For_::class,
            mutatorName: MutatorName::getName(For_::class),
            originalFilePath: '/path/to/src/Foo.php',
            originalStartingLine: 10,
            originalEndingLine: 15,
            originalCode: now(
                <<<'PHP'
                    <?php

                    namespace Acme;

                    class Foo
                    {
                        public function bar(): void
                        {
                            for ($i = 0; $i < 10; $i++) {
                                echo $i;
                            }
                        }
                    }

                    PHP,
            ),
            mutatedCode: now(
                <<<'PHP'
                    <?php

                    namespace Acme;

                    class Foo
                    {
                        public function bar(): void
                        {
                            // Mutated: removed for loop
                        }
                    }

                    PHP,
            ),
            tests: [
                new TestLocation(
                    'FooTest::test_it_can_do_something',
                    '/path/to/tests/FooTest.php',
                    0.123,
                ),
                new TestLocation(
                    'FooTest::test_it_can_do_something_else',
                    '/path/to/tests/FooTest.php',
                    0.456,
                ),
            ],
            processRuntime: 0.789,
        );
    }

    public function withProcessCommandLine(string $processCommandLine): self
    {
        $clone = clone $this;
        $clone->processCommandLine = $processCommandLine;

        return $clone;
    }

    public function withProcessOutput(string $processOutput): self
    {
        $clone = clone $this;
        $clone->processOutput = $processOutput;

        return $clone;
    }

    public function withDetectionStatus(DetectionStatus $detectionStatus): self
    {
        $clone = clone $this;
        $clone->detectionStatus = $detectionStatus;

        return $clone;
    }

    /**
     * @param Deferred<string>|string $mutantDiff
     */
    public function withMutantDiff(Deferred|string $mutantDiff): self
    {
        $clone = clone $this;
        $clone->mutantDiff = is_string($mutantDiff)
            ? now($mutantDiff)
            : $mutantDiff;

        return $clone;
    }

    public function withMutantHash(string $mutantHash): self
    {
        $clone = clone $this;
        $clone->mutantHash = $mutantHash;

        return $clone;
    }

    public function withMutatorClass(string $mutatorClass): self
    {
        $clone = clone $this;
        $clone->mutatorClass = $mutatorClass;

        return $clone;
    }

    public function withMutatorName(string $mutatorName): self
    {
        $clone = clone $this;
        $clone->mutatorName = $mutatorName;

        return $clone;
    }

    public function withOriginalFilePath(string $originalFilePath): self
    {
        $clone = clone $this;
        $clone->originalFilePath = $originalFilePath;

        return $clone;
    }

    public function withOriginalStartingLine(int $originalStartingLine): self
    {
        $clone = clone $this;
        $clone->originalStartingLine = $originalStartingLine;

        return $clone;
    }

    public function withOriginalEndingLine(int $originalEndingLine): self
    {
        $clone = clone $this;
        $clone->originalEndingLine = $originalEndingLine;

        return $clone;
    }

    /**
     * @param Deferred<string> $originalCode
     */
    public function withOriginalCode(Deferred $originalCode): self
    {
        $clone = clone $this;
        $clone->originalCode = $originalCode;

        return $clone;
    }

    /**
     * @param Deferred<string> $mutatedCode
     */
    public function withMutatedCode(Deferred $mutatedCode): self
    {
        $clone = clone $this;
        $clone->mutatedCode = $mutatedCode;

        return $clone;
    }

    /**
     * @param TestLocation[] $tests
     */
    public function withTests(array $tests): self
    {
        $clone = clone $this;
        $clone->tests = $tests;

        return $clone;
    }

    public function withProcessRuntime(float $processRuntime): self
    {
        $clone = clone $this;
        $clone->processRuntime = $processRuntime;

        return $clone;
    }

    public function build(): MutantExecutionResult
    {
        return new MutantExecutionResult(
            $this->processCommandLine,
            $this->processOutput,
            $this->detectionStatus,
            $this->mutantDiff,
            $this->mutantHash,
            $this->mutatorClass,
            $this->mutatorName,
            $this->originalFilePath,
            $this->originalStartingLine,
            $this->originalEndingLine,
            0,
            0,
            $this->originalCode,
            $this->mutatedCode,
            $this->tests,
            $this->processRuntime,
        );
    }
}
