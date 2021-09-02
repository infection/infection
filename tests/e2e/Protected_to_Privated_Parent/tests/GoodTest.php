<?php

namespace Protected_to_Privated_Parent\Test;

use Protected_to_Privated_Parent\Good;
use PHPUnit\Framework\TestCase;

class GoodTest extends TestCase
{
    public function test_hello()
    {
        $good = new Good();
        $this->assertSame(1, $good->getIntOuter());
    }
}
