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

namespace Infection\Tests\TestingUtility\Telemetry\TraceDumper\TestTraceDumper;

use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\SpanId;
use Infection\Telemetry\Tracing\Trace;
use Infection\Tests\Telemetry\Tracing\SpanBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestTraceDumper::class)]
final class TestTraceDumperTest extends TestCase
{
    #[DataProvider('traceProvider')]
    public function test_it_can_dump_a_trace(
        Trace $trace,
        string $expected,
    ): void {
        $dumper = new TestTraceDumper();

        $actual = $dumper->dump($trace);

        $this->assertSame($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        $firstRootId = SpanId::create(
            RootScope::ARTEFACT_COLLECTION,
            'firstRoot',
        );
        $secondRootId = SpanId::create(
            RootScope::SOURCE_FILE,
            'secondRoot',
        );

        $firstRoot = SpanBuilder::withRootTestData()
            ->withId($firstRootId)
            ->withChildren(
                SpanBuilder::withChildTestData()
                    ->withId(
                        SpanId::create(
                            Scope::INITIAL_TESTS,
                            'firstRoot-child1',
                            $firstRootId,
                        ),
                    )
                    ->build(),
                SpanBuilder::withChildTestData()
                    ->withId(
                        SpanId::create(
                            Scope::INITIAL_STATIC_ANALYSIS,
                            'firstRoot-child2',
                            $firstRootId,
                        ),
                    )
                    ->build(),
            )
            ->build();

        $secondRootChild1Id = SpanId::create(
            Scope::AST_GENERATION,
            'secondRoot-child1',
            $secondRootId,
        );
        $secondRoot = SpanBuilder::withRootTestData()
            ->withId($firstRootId)
            ->withChildren(
                SpanBuilder::withChildTestData()
                    ->withId($secondRootChild1Id)
                    ->withChildren(
                        SpanBuilder::withChildTestData()
                            ->withId(
                                SpanId::create(
                                    Scope::MUTATION_HEURISTICS,
                                    'secondRoot-child2-childA',
                                    $secondRootChild1Id,
                                ),
                            )
                            ->build(),
                        SpanBuilder::withChildTestData()
                            ->withId(
                                SpanId::create(
                                    Scope::MUTANT_EVALUATION,
                                    'secondRoot-child2-childB',
                                    $secondRootChild1Id,
                                ),
                            )
                            ->build(),
                    )
                    ->build(),
                SpanBuilder::withChildTestData()
                    ->withId(
                        SpanId::create(
                            Scope::MUTATION_EVALUATION,
                            'secondRoot-child2',
                            $secondRootId,
                        ),
                    )
                    ->build(),
            )
            ->build();

        yield [
            new Trace(
                'testTrace',
                [$firstRoot, $secondRoot],
            ),
            <<<'TRACE'
                ┌─ #:artefact_collection:firstRoot
                │   ├─ #:artefact_collection:firstRoot:initial_tests:firstRoot-child1
                │   └─ #:artefact_collection:firstRoot:initial_static_analysis:firstRoot-child2
                └─ #:artefact_collection:firstRoot
                    ├─ #:source_file:secondRoot:ast_generation:secondRoot-child1
                    │   ├─ #:source_file:secondRoot:ast_generation:secondRoot-child1:mutation_heuristics:secondRoot-child2-childA
                    │   └─ #:source_file:secondRoot:ast_generation:secondRoot-child1:mutant_evaluation:secondRoot-child2-childB
                    └─ #:source_file:secondRoot:mutation_evaluation:secondRoot-child2

                TRACE,
        ];
    }
}
