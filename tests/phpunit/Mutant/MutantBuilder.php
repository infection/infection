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

use Infection\Mutant\Mutant;
use Infection\Mutation\Mutation;
use Infection\Tests\Mutation\MutationBuilder;
use InvalidArgumentException;
use Later\Interfaces\Deferred;
use function Later\now;

final class MutantBuilder
{
    /**
     * @param Deferred<string> $mutatedCode
     * @param Deferred<string> $diff
     * @param Deferred<string> $prettyPrintedOriginalCode
     */
    private function __construct(
        private string $mutantFilePath,
        private Mutation $mutation,
        private Deferred $mutatedCode,
        private Deferred $diff,
        private Deferred $prettyPrintedOriginalCode,
    ) {
    }

    public static function from(Mutant $mutant): self
    {
        return new self(
            $mutant->getFilePath(),
            $mutant->getMutation(),
            $mutant->getMutatedCode(),
            $mutant->getDiff(),
            $mutant->getPrettyPrintedOriginalCode(),
        );
    }

    /**
     * @deprecated use `::withMinimalTestData()` or `::withCompleteTestData()` instead
     */
    public static function materialize(
        string $mutantFilePath = '/path/to/mutant',
        ?Mutation $mutation = null,
        string $mutatedCode = 'mutated code',
        string $diff = 'diff',
        string $prettyPrintedOriginalCode = '<?php $a = 1;',
    ): Mutant {
        if ($mutation === null) {
            throw new InvalidArgumentException('Mutation cannot be null');
        }

        return self::withMinimalTestData()
            ->withMutantFilePath($mutantFilePath)
            ->withMutation($mutation)
            ->withMutatedCode(now($mutatedCode))
            ->withDiff(now($diff))
            ->withPrettyPrintedOriginalCode(now($prettyPrintedOriginalCode))
            ->build();
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            mutantFilePath: '/path/to/mutant',
            mutation: MutationBuilder::withMinimalTestData()->build(),
            mutatedCode: now(
                <<<'PHP'
                    <?php $a = 2;
                    PHP,
            ),
            diff: now(
                <<<'PHP'
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                    PHP,
            ),
            prettyPrintedOriginalCode: now(
                <<<'PHP'
                    <?php $a = 1;
                    PHP,
            ),
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            mutantFilePath: '/path/to/src/mutants/Foo_mutant_0.php',
            mutation: MutationBuilder::withCompleteTestData()->build(),
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
            diff: now(
                <<<'PHP'
                    --- Original
                    +++ Mutated
                    @@ @@
                    -        for ($i = 0; $i < 10; $i++) {
                    -            echo $i;
                    -        }
                    +        // Mutated: removed for loop
                    PHP,
            ),
            prettyPrintedOriginalCode: now(
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
        );
    }

    public function withMutantFilePath(string $mutantFilePath): self
    {
        $clone = clone $this;
        $clone->mutantFilePath = $mutantFilePath;

        return $clone;
    }

    public function withMutation(Mutation $mutation): self
    {
        $clone = clone $this;
        $clone->mutation = $mutation;

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
     * @param Deferred<string> $diff
     */
    public function withDiff(Deferred $diff): self
    {
        $clone = clone $this;
        $clone->diff = $diff;

        return $clone;
    }

    /**
     * @param Deferred<string> $prettyPrintedOriginalCode
     */
    public function withPrettyPrintedOriginalCode(Deferred $prettyPrintedOriginalCode): self
    {
        $clone = clone $this;
        $clone->prettyPrintedOriginalCode = $prettyPrintedOriginalCode;

        return $clone;
    }

    public function build(): Mutant
    {
        return new Mutant(
            $this->mutantFilePath,
            $this->mutation,
            $this->mutatedCode,
            $this->diff,
            $this->prettyPrintedOriginalCode,
        );
    }
}
