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

use function count;
use function get_class;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use Infection\PhpParser\MutatedNode;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\Trace;
use function iterator_to_array;
use PhpParser\Node;
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class NodeMutationGenerator
{
    /** @var Mutator<Node>[] */
    private array $mutators;
    private string $filePath;
    /** @var Node[] */
    private array $fileNodes;
    private Trace $trace;
    private bool $onlyCovered;
    private LineRangeCalculator $lineRangeCalculator;

    private Node $currentNode;
    /** @var TestLocation[]|null */
    private ?array $testsMemoized = null;
    private ?bool $isOnFunctionSignatureMemoized = null;
    private ?bool $isInsideFunctionMemoized = null;

    /**
     * @param Mutator<Node>[] $mutators
     * @param Node[] $fileNodes
     */
    public function __construct(
        array $mutators,
        string $filePath,
        array $fileNodes,
        Trace $trace,
        bool $onlyCovered,
        LineRangeCalculator $lineRangeCalculator
    ) {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->mutators = $mutators;
        $this->filePath = $filePath;
        $this->fileNodes = $fileNodes;
        $this->trace = $trace;
        $this->onlyCovered = $onlyCovered;
        $this->lineRangeCalculator = $lineRangeCalculator;
    }

    /**
     * @return iterable<Mutation>
     */
    public function generate(Node $node): iterable
    {
        $this->currentNode = $node;
        $this->testsMemoized = null;
        $this->isOnFunctionSignatureMemoized = null;
        $this->isInsideFunctionMemoized = null;

        if (!$this->isOnFunctionSignature()
            && !$this->isInsideFunction()
        ) {
            return;
        }

        foreach ($this->mutators as $mutator) {
            yield from $this->generateForMutator($node, $mutator);
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
                $throwable
            );
        }

        $tests = $this->getAllTestsForCurrentNode();

        if ($this->onlyCovered && count($tests) === 0) {
            return;
        }

        $mutationByMutatorIndex = 0;

        foreach ($mutator->mutate($node) as $mutatedNode) {
            yield new Mutation(
                $this->filePath,
                $this->fileNodes,
                $mutator->getName(),
                $node->getAttributes(),
                get_class($node),
                MutatedNode::wrap($mutatedNode),
                $mutationByMutatorIndex,
                $tests
            );

            ++$mutationByMutatorIndex;
        }
    }

    private function isOnFunctionSignature(): bool
    {
        return $this->isOnFunctionSignatureMemoized ??
            $this->isOnFunctionSignatureMemoized = $this->currentNode->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
    }

    private function isInsideFunction(): bool
    {
        return $this->isInsideFunctionMemoized ??
            $this->isInsideFunctionMemoized = $this->currentNode->getAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, false);
    }

    /**
     * @return TestLocation[]
     */
    private function getAllTestsForCurrentNode(): array
    {
        if ($this->testsMemoized !== null) {
            return $this->testsMemoized;
        }

        $testsMemoized = $this->trace->getAllTestsForMutation(
            $this->lineRangeCalculator->calculateRange($this->currentNode),
            $this->isOnFunctionSignature()
        );

        if ($testsMemoized instanceof Traversable) {
            $testsMemoized = iterator_to_array($testsMemoized, false);
        }

        return $this->testsMemoized = $testsMemoized;
    }
}
