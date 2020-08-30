<?php

namespace TimeoutSkipped\Test;

use TimeoutSkipped\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_it_adds_2_numbers(): void
    {
        $source = new SourceClass();

        $result = $source->add(1, 2);

        self::assertSame(3, $result);
    }
}
