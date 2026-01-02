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

namespace Infection\Mutator;

use Infection\Ast\Metadata\NodeAnnotator;
use Infection\Mutation\Mutation;
use Infection\PhpParser\MutatedNode;
use Infection\Source\Exception\NoSourceFound;
use PhpParser\Node;
use PhpParser\Token;
use Throwable;

/**
 * @internal
 */
final readonly class NodeMutationGenerator
{
    /**
     * @param Mutator<Node>[] $mutators
     * @param Node[] $fileNodes
     * @param Token[] $originalFileTokens
     */
    public function __construct(
        private Mutators $mutators,
        private string $filePath,
        private array $fileNodes,
        private array $originalFileTokens,
        private string $originalFileContent,
    ) {
    }

    /**
     * @throws NoSourceFound
     *
     * @return iterable<Mutation>
     */
    public function generate(Node $node): iterable
    {
        if (NodeAnnotator::isEligible($node)) {
            foreach ($this->mutators as $mutator) {
                yield from $this->generateForMutator($node, $mutator);
            }
        }
    }

    /**
     * @param Mutator<Node> $mutator
     *
     * @return iterable<Mutation>
     */
    private function generateForMutator(Node $node, Mutator $mutator): iterable
    {
        try {
            if (!$mutator->canMutate($node)) {
                return;
            }
        } catch (Throwable $throwable) {
            throw InvalidMutator::create(
                $this->filePath,
                $mutator->getName(),
                $throwable,
            );
        }

        $mutationByMutatorIndex = 0;

        foreach ($mutator->mutate($node) as $mutatedNode) {
            yield new Mutation(
                $this->filePath,
                $this->fileNodes,
                $mutator::class,
                $mutator->getName(),
                $node->getAttributes(),
                $node::class,
                MutatedNode::wrap($mutatedNode),
                $mutationByMutatorIndex,
                NodeAnnotator::getTests($node),
                $this->originalFileTokens,
                $this->originalFileContent,
            );

            ++$mutationByMutatorIndex;
        }
    }
}
