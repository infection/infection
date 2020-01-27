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

use function array_reduce;
use function count;
use function get_class;
use Infection\MutatedNode;
use Infection\Mutation\Mutation;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use function iterator_to_array;
use PhpParser\Node;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class NodeMutationGenerator
{
    private $mutators;
    private $filePath;
    private $fileNodes;
    private $codeCoverageData;
    private $onlyCovered;

    /**
     * @param Mutator[] $mutators
     * @param Node[] $fileNodes
     */
    public function __construct(
        array $mutators,
        string $filePath,
        array $fileNodes,
        LineCodeCoverage $codeCoverageData,
        bool $onlyCovered
    ) {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->fileNodes = $fileNodes;
        $this->codeCoverageData = $codeCoverageData;
        $this->onlyCovered = $onlyCovered;
    }

    /**
     * @return Mutation[]
     */
    public function generate(Node $node): array
    {
        return array_reduce(
            $this->mutators,
            function (array $mutations, Mutator $mutator) use ($node): array {
                return $this->generateForMutator($node, $mutator, $mutations);
            },
            []
        );
    }

    /**
     * @param Mutation[] $mutations
     *
     * @return Mutation[]
     */
    private function generateForMutator(Node $node, Mutator $mutator, array $mutations): array
    {
        try {
            if (!$mutator->canMutate($node)) {
                return $mutations;
            }
        } catch (Throwable $throwable) {
            throw InvalidMutator::create(
                $this->filePath,
                $mutator->getName(),
                $throwable
            );
        }

        $isOnFunctionSignature = $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);

        if (!$isOnFunctionSignature
            && !$node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY)
        ) {
            return $mutations;
        }

        $tests = $this->codeCoverageData->getAllTestsForMutation(
            $this->filePath,
            $this->getNodeRange($node, $isOnFunctionSignature),
            $isOnFunctionSignature
        );

        if ($this->onlyCovered && count($tests) === 0) {
            return $mutations;
        }

        // It is important to not rely on the keys here. It might otherwise result in some elements
        // being overridden, see https://3v4l.org/JLN73
        $mutatedNodes = iterator_to_array($mutator->mutate($node), false);

        foreach ($mutatedNodes as $mutationByMutatorIndex => $mutatedNode) {
            $mutations[] = new Mutation(
                $this->filePath,
                $this->fileNodes,
                $mutator->getName(),
                $node->getAttributes(),
                get_class($node),
                MutatedNode::wrap($mutatedNode),
                $mutationByMutatorIndex,
                $tests
            );
        }

        return $mutations;
    }

    /**
     * If the node is part of an array, this will find the outermost array.
     * Otherwise this will return the node itself
     */
    private function getOuterMostArrayNode(Node $node): Node
    {
        $outerMostArrayParent = $node;

        do {
            if ($node instanceof Node\Expr\Array_) {
                $outerMostArrayParent = $node;
            }
        } while ($node = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY));

        return $outerMostArrayParent;
    }

    private function getNodeRange(Node $node, bool $isOnFunctionSignature): NodeLineRangeData
    {
        if ($isOnFunctionSignature) {
            $node = $this->getOuterMostArrayNode($node);
        }

        return new NodeLineRangeData($node->getStartLine(), $node->getEndLine());
    }
}
