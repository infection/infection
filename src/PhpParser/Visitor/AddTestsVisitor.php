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

namespace Infection\PhpParser\Visitor;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\Mutator;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use function iterator_to_array;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Token;
use Traversable;
use Webmozart\Assert\Assert;

final class AddTestsVisitor extends NodeVisitorAbstract
{
    private const TESTS = 'tests';

    /**
     * @param Mutator<Node>[] $mutators
     * @param Node[] $fileNodes
     * @param Token[] $originalFileTokens
     */
    public function __construct(
        array $mutators,
        private readonly string $filePath,
        private readonly array $fileNodes,
        private readonly Trace $trace,
        private readonly bool $onlyCovered,
        private readonly LineRangeCalculator $lineRangeCalculator,
        private readonly SourceLineMatcher $sourceLineMatcher,
        private readonly array $originalFileTokens,
        private readonly string $originalFileContent,
    ) {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->mutators = $mutators;
    }

    public function enterNode(Node $node): null
    {
        $node->setAttribute(
        );

        return null;
    }

    /**
     * @return TestLocation[]
     */
    private function getAllTestsForNode(Node $node): array
    {
        if ($this->testsMemoized !== null) {
            return $this->testsMemoized;
        }

        $testsMemoized = $this->trace->getAllTestsForMutation(
            $this->lineRangeCalculator->calculateRange($this->currentNode),
            $this->isOnFunctionSignature(),
        );

        if ($testsMemoized instanceof Traversable) {
            $testsMemoized = iterator_to_array($testsMemoized, false);
        }

        return $this->testsMemoized = $testsMemoized;
    }
}
