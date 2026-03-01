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

namespace newSrc\Mutagenesis\Strategy;

use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_slice;
use function iter\toArray;
use newSrc\Mutation\NodeVisitor\MutationCollectorVisitor;
use PhpParser\Node;
use Random\Engine\Mt19937;
use Random\Randomizer;
use SplObjectStorage;

/**
 * @phpstan-import-type MutationFactory from MutationCollectorVisitor
 */
final readonly class RandomSelectionStrategy implements Strategy
{
    /**
     * @param positive-int $limit
     */
    public function __construct(
        private int $seed,
        private int $limit,
    ) {
    }

    public function apply(SplObjectStorage $potentialMutations): iterable
    {
        $selectedOffsets = $this->selectOffsets($potentialMutations);

        foreach ($selectedOffsets as $node) {
            $createMutation = $potentialMutations[$node];

            yield from $createMutation($node);
        }
    }

    /**
     * @template T keyof SplObjectStorage
     *
     * @param SplObjectStorage<Node, MutationFactory> $potentialMutations
     *
     * @return Node[]
     */
    private function selectOffsets(SplObjectStorage $potentialMutations): array
    {
        $offsets = toArray($potentialMutations);

        $engine = new Mt19937($this->seed);
        $randomizer = new Randomizer($engine);

        $keys = array_keys($offsets);
        $shuffledKeys = $randomizer->shuffleArray($keys);

        $selectedKeys = array_slice($shuffledKeys, 0, $this->limit);

        return array_intersect_key(
            $offsets,
            array_flip($selectedKeys),
        );
    }
}
