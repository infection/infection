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

namespace Infection\Visitor;

use PhpParser\Node\Expr\Array_;
use function count;
use Generator;
use function get_class;
use Infection\Exception\InvalidMutatorException;
use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Throwable;

/**
 * @internal
 */
final class MutationsCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var Mutation[]
     */
    private $mutations = [];

    private $mutators;
    private $filePath;
    private $fileAst;
    private $codeCoverageData;
    private $onlyCovered;

    /**
     * @param Mutator[] $mutators
     * @param  Node[] $fileAst
     */
    public function __construct(
        array $mutators,
        string $filePath,
        array $fileAst,
        LineCodeCoverage $codeCoverageData,
        bool $onlyCovered
    ) {
        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->fileAst = $fileAst;
        $this->codeCoverageData = $codeCoverageData;
        $this->onlyCovered = $onlyCovered;
    }

    public function leaveNode(Node $node): ?Node
    {
        foreach ($this->mutators as $mutator) {
            try {
                if (!$mutator->shouldMutate($node)) {
                    continue;
                }
            } catch (Throwable $t) {
                throw InvalidMutatorException::create($this->filePath, $mutator, $t);
            }

            $isOnFunctionSignature = $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);

            if (!$isOnFunctionSignature
                && !$node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY)
            ) {
                continue;
            }

            $tests = $this->codeCoverageData
                ->getAllTestsForMutation(
                    $this->filePath,
                    $this->getNodeRange($node, $isOnFunctionSignature),
                    $isOnFunctionSignature
                );

            if ($this->onlyCovered && count($tests) === 0) {
                continue;
            }

            $mutatedResult = $mutator->mutate($node);

            $mutatedNodes = $mutatedResult instanceof Generator ? $mutatedResult : [$mutatedResult];

            foreach ($mutatedNodes as $mutationByMutatorIndex => $mutatedNode) {
                $this->mutations[] = new Mutation(
                    $this->filePath,
                    $this->fileAst,
                    $mutator,
                    $node->getAttributes(),
                    get_class($node),
                    $mutatedNode,
                    $mutationByMutatorIndex,
                    $tests
                );
            }
        }

        return null;
    }

    /**
     * @return Mutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }

    /**
     * If the node is part of an array, this will find the outermost array.
     * Otherwise this will return the node itself
     */
    private function getOuterMostArrayNode(Node $node): Node
    {
        $outerMostArrayParent = $node;

        do {
            if ($node instanceof Array_) {
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
