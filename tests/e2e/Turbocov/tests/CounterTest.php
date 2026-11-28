<?php

namespace Infection\E2ETests\Turbocov\Tests;

use Infection\E2ETests\Turbocov\Lib\Counter;
use PHPUnit\Framework\TestCase;

class CounterTest extends TestCase
{
    public function test_next(): void
    {
        $this->assertSame(2, (new Counter())->next(1));
    }
}
