<?php

namespace Infection\Tests\NewSrc\AST\Metadata;

use newSrc\AST\Metadata\NodePosition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NodePositionBuilder::class)]
final class NodePositionBuilderTest extends TestCase
{
    public function test_it_can_build_a_node_position(): void
    {
        $builder = NodePositionBuilder::multiLineWithTestData();

        $newBuilder = $builder
            ->withStartLine(20)
            ->withStartTokenPosition(5)
            ->withEndLine(30)
            ->withEndTokenPosition(132);

        // Test immutability
        self::assertEquals(
            NodePositionBuilder::multiLineWithTestData()->build(),
            $builder->build(),
        );

        self::assertEquals(
            new NodePosition(20, 5, 30, 132),
            $newBuilder->build(),
        );
    }

    public function test_it_can_be_created_from_an_existing_instance(): void
    {
        $expected = new NodePosition(1, 2, 3, 4);

        $actual = NodePositionBuilder::from($expected)->build();

        self::assertEquals($expected, $actual);
    }
}
