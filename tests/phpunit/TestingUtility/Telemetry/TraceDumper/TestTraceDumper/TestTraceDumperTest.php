<?php

namespace Infection\Tests\TestingUtility\Telemetry\TraceDumper\TestTraceDumper;

use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\Span;
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
    ): void
    {
        $dumper = new TestTraceDumper();

        $actual = $dumper->dump($trace);

        self::assertSame($expected, $actual);
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
                                )
                            )
                            ->build(),
                        SpanBuilder::withChildTestData()
                            ->withId(
                                SpanId::create(
                                    Scope::MUTANT_EVALUATION,
                                    'secondRoot-child2-childB',
                                    $secondRootChild1Id,
                                )
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
