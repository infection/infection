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

namespace Infection\TestFramework;

use function array_map;
use function implode;
use Infection\Mutant\Mutant;
use Infection\TestFramework\Common\LazyMutantEvaluationPipe;
use Infection\TestFramework\Contracts\CompositeInitialRunResults;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class CompositeTestFramework implements TestFramework
{
    /**
     * @param non-empty-list<TestFramework> $testFrameworks
     */
    public function __construct(
        private array $testFrameworks,
    ) {
    }

    public function getName(): string
    {
        return sprintf(
            'Composite(%s)',
            implode(
                ', ',
                array_map(
                    static fn (TestFramework $testFramework): string => $testFramework->getName(),
                    $this->testFrameworks,
                ),
            ),
        );
    }

    public function getVersion(): string
    {
        return implode(
            ', ',
            array_map(
                static fn (TestFramework $testFramework): string => $testFramework->getVersion(),
                $this->testFrameworks,
            ),
        );
    }

    public function checkRequirements(): void
    {
        foreach ($this->testFrameworks as $testFramework) {
            $testFramework->checkRequirements();
        }
    }

    public function executeInitialRun(): CompositeInitialRunResults
    {
        return new CompositeInitialRunResults(
            array_map(
                static function (TestFramework $testFramework): array {
                    $result = $testFramework->executeInitialRun();
                    Assert::isInstanceOf($result, InitialRunResults::class);

                    return [$testFramework, $result];
                },
                $this->testFrameworks,
            ),
        );
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        return LazyMutantEvaluationPipe::merge(
            array_map(
                static function (TestFramework $testFramework) use ($mutant): LazyMutantEvaluationPipe {
                    $pipe = $testFramework->test($mutant);
                    Assert::isInstanceOf($pipe, LazyMutantEvaluationPipe::class);

                    return $pipe;
                },
                $this->testFrameworks,
            ),
        );
    }
}
