<?php

namespace Codeception_Basic\Test;

use Codeception\Test\Unit;
use Codeception_Basic\Math;

class MathTest extends Unit
{
    private $math;

    protected function _before()
    {
        $this->math = new Math();
    }

    public function testAdd()
    {
        $this::assertSame(3, $this->math->add(1, 2));
    }
}
