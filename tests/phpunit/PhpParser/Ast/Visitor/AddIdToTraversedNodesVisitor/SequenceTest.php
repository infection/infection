<?php

namespace Infection\Tests\PhpParser\Ast\Visitor\AddIdToTraversedNodesVisitor;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sequence::class)]
final class SequenceTest extends TestCase
{
    public function test_it_gives_a_sequence(): void
    {
        $sequence = new Sequence();

        for ($i = 0; $i < 10; $i++) {
            $value = $sequence->next();

            $this->assertSame($i, $value);
        }
    }
}
