<?php

namespace TimeoutSkippedByFastestTest\Test;

use PHPUnit\Framework\TestCase;
use TimeoutSkippedByFastestTest\SourceClass;

class SlowFirstTest extends TestCase
{
    public function test_it_works_slowly(): void
    {
        // Over the 2 seconds timeout on its own
        usleep(2100000);

        $source = new SourceClass();

        self::assertSame(3, $source->add(1, 2));
        self::assertTrue($source->isPositive(5));
        self::assertSame(1, $source->decrement(2));
    }
}
