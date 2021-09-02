<?php

namespace Protected_to_Privated_Parent\Test;

use Protected_to_Privated_Parent\Bad;
use PHPUnit\Framework\TestCase;

class BadTest extends TestCase
{
    public function test_hello()
    {
        $good = new Bad();
        $this->assertSame(2, $good->getIntOuter());
    }
}
