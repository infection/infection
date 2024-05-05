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

namespace Infection\Mutator\Removal;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements Mutator<Node\Stmt\Switch_>
 */
final class SharedCaseRemoval implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Removes `case`s from `switch`.',
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                switch ($x) {
                -   case 1:
                    case 2:
                        fooBar();
                        break;
                    default:
                        baz();
                }
                DIFF,
        );
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Switch_) {
            return false;
        }

        foreach ($node->cases as $case) {
            if ($case->stmts === []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Stmt\Switch_>
     */
    public function mutate(Node $node): iterable
    {
        $previousWasEmpty = false;

        foreach ($node->cases as $i => $case) {
            if ($case->stmts === []) {
                $previousWasEmpty = true;
                $cases = $node->cases;
                unset($cases[$i]);

                yield new Node\Stmt\Switch_(
                    $node->cond,
                    $cases,
                    $node->getAttributes(),
                );

                continue;
            }

            if ($previousWasEmpty) {
                $previousWasEmpty = false;
                $cases = $node->cases;
                unset($cases[$i]);
                $lastCase = $cases[$i - 1];
                $cases[$i - 1] = new Node\Stmt\Case_(
                    $lastCase->cond,
                    $case->stmts,
                    $lastCase->getAttributes(),
                );

                yield new Node\Stmt\Switch_(
                    $node->cond,
                    $cases,
                    $node->getAttributes(),
                );
            }
        }
    }
}
