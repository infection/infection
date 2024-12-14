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

use function count;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Definition;
use Infection\Mutator\GetConfigClassName;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\MutatorCategory;
use Infection\PhpParser\Visitor\ParentConnector;
use function min;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use function range;

/**
 * @internal
 *
 * @implements ConfigurableMutator<Node\Expr\Array_>
 */
final readonly class ArrayItemRemoval implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;

    public function __construct(private ArrayItemRemovalConfig $config)
    {
    }

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Removes an element of an array literal. For example:

                ```php
                $x = [0, 1, 2];
                ```

                Will be mutated to:

                ```php
                $x = [1, 2];
                ```

                And:

                ```php
                $x = [0, 2];
                ```

                And:

                ```php
                $x = [0, 1];
                ```

                Which elements it removes or how many elements it will attempt to remove will depend on its
                configuration.

                TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - $x = [0, 1, 2];
                # Mutation 1
                + $x = [1, 2];
                # Mutation 2
                + $x = [0, 2];
                # Mutation 3
                + $x = [0, 1];
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr\Array_>
     */
    public function mutate(Node $node): iterable
    {
        foreach ($this->getItemsIndexes($node->items) as $indexToRemove) {
            $newArrayNode = clone $node;
            unset($newArrayNode->items[$indexToRemove]);

            yield $newArrayNode;
        }
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\Array_) {
            return false;
        }

        if ($node->items === []) {
            return false;
        }

        $parent = ParentConnector::findParent($node);

        // Arrays to the left of an assignments are not arrays but lists.
        if ($parent instanceof Node\Expr\Assign && $parent->var === $node) {
            return false;
        }

        if ($parent instanceof Node\Arg && ParentConnector::findParent($parent) instanceof Node\Attribute) {
            return false;
        }

        // Don't mutate destructured values in foreach loops
        if ($parent instanceof Node\Stmt\Foreach_ && $parent->valueVar === $node) {
            return false;
        }

        return true;
    }

    /**
     * @psalm-mutation-free
     *
     * @param array<array-key, ArrayItem|null> $items
     *
     * @return int[]
     */
    private function getItemsIndexes(array $items): array
    {
        return match ($this->config->getRemove()) {
            'first' => [0],
            'last' => [count($items) - 1],
            default => range(
                0,
                min(count($items),
                    $this->config->getLimit()) - 1,
            ),
        };
    }
}
