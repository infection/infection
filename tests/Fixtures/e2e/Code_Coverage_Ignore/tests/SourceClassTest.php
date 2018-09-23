<?php

namespace Namespace_\Test;

use Namespace_\IgnoreClass;
use PHPUnit\Framework\TestCase;

class IgnoreClassTest extends TestCase
{
    public function test_hello()
    {
        $ignoreClass = new IgnoreClass();
        $this->assertSame(3, $ignoreClass->getThree());
    }
}
