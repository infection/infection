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

namespace Infection\Tests\PhpParser\Visitor\AddTestsVisitor;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\PhpParser\Visitor\AddTestsVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddTestsVisitor::class)]
final class AddTestsVisitorTest extends TestCase
{
    #[DataProvider('scenarioProvider')]
    public function test_it_adds_tests_to_eligible_nodes(Scenario $scenario): void
    {
        $node = self::createNode(
            $scenario->isEligible,
            $scenario->isOnFunctionSignature,
        );

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->expects($this->exactly($scenario->expectedTraceCallCount))
            ->method('getAllTestsForMutation')
            ->with(
                $this->anything(),
                $scenario->isOnFunctionSignature ?? false,
            )
            ->willReturn($scenario->traceTests);

        $visitor = new AddTestsVisitor(
            $traceMock,
            new LineRangeCalculator(),
        );
        $visitor->enterNode($node);

        $actual = AddTestsVisitor::getTests($node);

        $this->assertSame($scenario->expectedTests, $actual);
        $this->assertSame($scenario->expectedHasTests, AddTestsVisitor::hasTests($node));
    }

    #[DataProvider('scenarioProvider')]
    public function test_it_does_not_evaluate_the_tests_until_called(Scenario $scenario): void
    {
        $node = self::createNode(
            $scenario->isEligible,
            $scenario->isOnFunctionSignature,
        );

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->expects($this->never())
            ->method('getAllTestsForMutation');

        $visitor = new AddTestsVisitor(
            $traceMock,
            new LineRangeCalculator(),
        );

        $visitor->enterNode($node);
    }

    public static function scenarioProvider(): iterable
    {
        $testA = new TestLocation(
            'TestA::test_a',
            '/path/to/TestA.php',
            0.5,
        );
        $testB = new TestLocation(
            'TestB::test_b',
            '/path/to/TestB.php',
            1.0,
        );

        $scenario = new Scenario(
            isEligible: null,
            isOnFunctionSignature: null,
            traceTests: [],
            expectedTraceCallCount: 0,
            expectedTests: [],
        );

        yield 'no state' => [$scenario];

        yield 'eligible' => [
            $scenario
                ->withIsEligible(true)
                ->withExpectedTraceCallCount(1),
        ];

        yield 'ineligible' => [
            $scenario->withIsEligible(false),
        ];

        yield 'eligible with tests' => [
            $scenario
                ->withIsEligible(true)
                ->withTraceTests([$testA, $testB])
                ->withExpectedTraceCallCount(1)
                ->withExpectedTests([$testA, $testB]),
        ];

        yield 'eligible with single test' => [
            $scenario
                ->withIsEligible(true)
                ->withTraceTests([$testA])
                ->withExpectedTraceCallCount(1)
                ->withExpectedTests([$testA]),
        ];

        yield 'ineligible with tests' => [
            $scenario
                ->withIsEligible(false)
                ->withTraceTests([$testA]),
        ];

        yield 'eligible on function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(true)
                ->withTraceTests([$testA])
                ->withExpectedTraceCallCount(1)
                ->withExpectedTests([$testA]),
        ];

        yield 'eligible not on function signature' => [
            $scenario
                ->withIsEligible(true)
                ->withIsOnFunctionSignature(false)
                ->withTraceTests([$testA])
                ->withExpectedTraceCallCount(1)
                ->withExpectedTests([$testA]),
        ];
    }

    private static function createNode(
        ?bool $isEligible,
        ?bool $isOnFunctionSignature,
    ): Node {
        $node = new Variable('dummy');
        $node->setAttributes([
            'startLine' => 10,
            'endLine' => 12,
        ]);

        if ($isEligible !== null) {
            $isEligible
                ? LabelNodesAsEligibleVisitor::markAsEligible($node)
                : LabelNodesAsEligibleVisitor::markAsIneligible($node);
        }

        if ($isOnFunctionSignature !== null) {
            $node->setAttribute(ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE, $isOnFunctionSignature);
        }

        return $node;
    }
}
