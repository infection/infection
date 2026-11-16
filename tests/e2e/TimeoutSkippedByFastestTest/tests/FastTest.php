<?php

namespace TimeoutSkippedByFastestTest\Test;

use PHPUnit\Framework\TestCase;
use TimeoutSkippedByFastestTest\SourceClass;

class FastTest extends TestCase
{
    public function test_it_adds_2_numbers(): void
    {
        $source = new SourceClass();

        self::assertSame(3, $source->add(1, 2));
        self::assertTrue($source->isPositive(5));
    }
}
