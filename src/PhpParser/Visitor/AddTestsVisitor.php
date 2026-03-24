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

use Closure;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function Pipeline\take;

final class AddTestsVisitor extends NodeVisitorAbstract
{
    private const TESTS = 'tests';

    public function __construct(
        private readonly Trace $trace,
        private readonly LineRangeCalculator $lineRangeCalculator,
    ) {
    }

    public function enterNode(Node $node): null
    {
        if (LabelNodesAsEligibleVisitor::isEligible($node)) {
            $node->setAttribute(
                self::TESTS,
                $this->getAllTestsForNodeCl($node),
            );
        }

        return null;
    }

    /**
     * @return TestLocation[]
     */
    public static function getTests(Node $node): array
    {
        return $node->getAttribute(self::TESTS, default: [])();
    }

    /**
     * @return Closure():TestLocation[]
     */
    private function getAllTestsForNodeCl(Node $node): Closure
    {
        return function (Node $node) {
            static $tests;

            if (!isset($tests)) {
                $tests = $this->getAllTestsForNode($node);
            }

            return $tests;
        };
    }

    /**
     * @return list<TestLocation>
     */
    private function getAllTestsForNode(Node $node): array
    {
        $tests = $this->trace->getAllTestsForMutation(
            $this->lineRangeCalculator->calculateRange($node),
            $this->isOnFunctionSignature($node),
        );

        return take($tests)->toList();
    }

    private function isOnFunctionSignature(Node $node): bool
    {
        return $node->getAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, false);
    }
}
