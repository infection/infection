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

namespace Infection\Tests\PhpParser\Visitor\ExcludeNonMutableCodeVisitor;

use Infection\PhpParser\Visitor\ExcludeNonMutableCodeVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExcludeNonMutableCodeVisitor::class)]
final class ExcludeNonMutableCodeVisitorTest extends TestCase
{
    #[DataProvider('scenarioProvider')]
    public function test_it_marks_nodes_not_belonging_to_functions_ineligible(Scenario $scenario): void
    {
        $node = self::createNode(
            $scenario->isEligible,
            $scenario->isOnFunctionSignature,
            $scenario->isInsideFunctionSignature,
        );

        $visitor = new ExcludeNonMutableCodeVisitor();
        $visitor->enterNode($node);

        $actual = LabelNodesAsEligibleVisitor::isEligible($node);

        $this->assertSame($scenario->expected, $actual);
    }

    public static function scenarioProvider(): iterable
    {
        $scenario = new Scenario(
            isEligible: null,
            isOnFunctionSignature: null,
            isInsideFunctionSignature: null,
            expected: false,
        );

        yield 'no state' => [$scenario];

        yield 'eligible' => [
            $scenario->withIsEligible(true),
        ];

        yield 'ineligible' => [
            $scenario->withIsEligible(false),
        ];

        yield 'on function signature' => [
            $scenario->withIsOnFunctionSignature(true),
        ];

        yield 'not on function signature' => [
            $scenario->withIsOnFunctionSignature(false),
        ];

        yield 'eligible on function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(true)
                ->withExpected(true),
        ];

        yield 'eligible not on function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(false),
        ];

        yield 'ineligible on function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(true),
        ];

        yield 'ineligible not on function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(false),
        ];

        yield 'inside function signature' => [
            $scenario->withIsInsideFunctionSignature(true),
        ];

        yield 'not inside function signature' => [
            $scenario->withIsInsideFunctionSignature(false),
        ];

        yield 'eligible inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsInsideFunctionSignature(true)
                ->withExpected(true),
        ];

        yield 'eligible not inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'ineligible inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsInsideFunctionSignature(true),
        ];

        yield 'ineligible not inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'on function signature and inside function signature' => [
            $scenario
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(true),
        ];

        yield 'not on function signature and inside function signature' => [
            $scenario
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(true),
        ];

        yield 'on function signature and not inside function signature' => [
            $scenario
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'not on function signature and not inside function signature' => [
            $scenario
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'eligible on function signature and inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(true)
                ->withExpected(true),
        ];

        yield 'eligible not on function signature and inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(true)
                ->withExpected(true),
        ];

        yield 'eligible on function signature and not inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(false)
                ->withExpected(true),
        ];

        yield 'eligible not on function signature and not inside function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'ineligible on function signature and inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(true),
        ];

        yield 'ineligible not on function signature and inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(true),
        ];

        yield 'ineligible on function signature and not inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(true)
                ->withIsInsideFunctionSignature(false),
        ];

        yield 'ineligible not on function signature and not inside function signature' => [
            $scenario
                ->withIsEligible(false)
                ->withIsOnFunctionSignature(false)
                ->withIsInsideFunctionSignature(false),
        ];
    }

    private static function createNode(
        ?bool $isEligible,
        ?bool $isOnFunctionSignature,
        ?bool $isInsideFunctionSignature,
    ): Node {
        $node = new Variable('dummy');

        if ($isEligible !== null) {
            $isEligible
                ? LabelNodesAsEligibleVisitor::markAsEligible($node)
                : LabelNodesAsEligibleVisitor::markAsIneligible($node);
        }

        if ($isOnFunctionSignature !== null) {
            $node->setAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, $isOnFunctionSignature);
        }

        if ($isInsideFunctionSignature !== null) {
            $node->setAttribute(ReflectionVisitor::IS_INSIDE_FUNCTION_KEY, $isInsideFunctionSignature);
        }

        return $node;
    }
}
