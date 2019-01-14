<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

use Infection\Exception\InvalidMutatorException;
use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class MutationsCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var Mutator[]
     */
    private $mutators = [];

    /**
     * @var Mutation[]
     */
    private $mutations = [];

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Node[]
     */
    private $fileAst;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;
    /**
     * @var bool
     */
    private $onlyCovered;

    public function __construct(
        array $mutators,
        string $filePath,
        array $fileAst,
        CodeCoverageData $codeCoverageData,
        bool $onlyCovered
    ) {
        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->fileAst = $fileAst;
        $this->codeCoverageData = $codeCoverageData;
        $this->onlyCovered = $onlyCovered;
    }

    public function leaveNode(Node $node): void
    {
        foreach ($this->mutators as $mutator) {
            try {
                if (!$mutator->shouldMutate($node)) {
                    continue;
                }
            } catch (\Throwable $t) {
                throw InvalidMutatorException::create($this->filePath, $mutator, $t);
            }

            $isOnFunctionSignature = $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);

            if (!$isOnFunctionSignature
                && !$node->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY)
            ) {
                continue;
            }

            if ($isOnFunctionSignature
                && $methodNode = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY)
            ) {
                /** @var Node\Stmt\ClassMethod|Node\Expr\Closure $methodNode */
                if ($methodNode instanceof Node\Stmt\ClassMethod && $methodNode->isAbstract()) {
                    continue;
                }

                if ($methodNode instanceof Node\Stmt\ClassMethod && $methodNode->getAttribute(ParentConnectorVisitor::PARENT_KEY) instanceof Node\Stmt\Interface_) {
                    continue;
                }
            }

            $isCoveredByTest = $this->isCoveredByTest($isOnFunctionSignature, $node);

            if ($this->onlyCovered && !$isCoveredByTest) {
                continue;
            }

            $mutatedResult = $mutator->mutate($node);

            $mutatedNodes = $mutatedResult instanceof \Generator ? $mutatedResult : [$mutatedResult];

            foreach ($mutatedNodes as $mutationByMutatorIndex => $mutatedNode) {
                $this->mutations[] = new Mutation(
                    $this->filePath,
                    $this->fileAst,
                    $mutator,
                    $node->getAttributes(),
                    \get_class($node),
                    $isOnFunctionSignature,
                    $isCoveredByTest,
                    $mutatedNode,
                    $mutationByMutatorIndex
                );
            }
        }
    }

    /**
     * @return Mutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }

    private function isCoveredByTest(bool $isOnFunctionSignature, Node $node): bool
    {
        if ($isOnFunctionSignature) {
            // hasExecutedMethodOnLine checks for all lines of a given method,
            // therefore it isn't making any sense to check any other line but first
            return $this->codeCoverageData->hasExecutedMethodOnLine($this->filePath, $node->getLine());
        }

        for ($line = $node->getStartLine(); $line <= $node->getEndLine(); ++$line) {
            if ($this->codeCoverageData->hasTestsOnLine($this->filePath, $line)) {
                return true;
            }
        }

        return false;
    }
}
