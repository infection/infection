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

use function array_values;
use function count;
use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\Match_>
 */
final class MatchArmRemoval implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Removes `match arm`s from `match`.

                ```php
                match ($x) {
                    'cond1', 'cond2' => true,
                    default => throw new \Exception(),
                };
                ```

                Will be mutated to:

                ```php
                match ($x) {
                    'cond1' => true,
                    default => throw new \Exception(),
                };
                ```

                ```php
                match ($x) {
                    'cond2' => true,
                    default => throw new \Exception(),
                };
                ```

                And:
                ```php
                match ($x) {
                    default => throw new \Exception(),
                };
                ```
                TXT,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                match ($x) {
                -   0 => false,
                    1 => true,
                    2 => null,
                    default => throw new \Exception(),
                };
                DIFF,
        );
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof Node\Expr\Match_
            && count($node->arms) > 1;
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr\Match_>
     */
    public function mutate(Node $node): iterable
    {
        foreach ($node->arms as $i => $arm) {
            $arms = $node->arms;

            $armConds = $arm->conds ?? [];

            if (count($armConds) > 1) {
                foreach ($armConds as $j => $cond) {
                    $conds = $armConds;

                    unset($conds[$j]);

                    $arms[$i] = new Node\MatchArm(array_values($conds), $arm->body, $node->getAttributes());

                    yield new Node\Expr\Match_($node->cond, $arms, $node->getAttributes());
                }

                continue;
            }

            unset($arms[$i]);

            yield new Node\Expr\Match_($node->cond, $arms, $node->getAttributes());
        }
    }
}
