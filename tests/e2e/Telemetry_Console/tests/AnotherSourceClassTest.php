<?php

namespace Ignore_All_Mutations\Test;

use Ignore_All_Mutations\AnotherSourceClass;
use PHPUnit\Framework\TestCase;

class AnotherSourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new AnotherSourceClass();
        $this->assertSame('hello', $sourceClass->hello(100));
        $this->assertSame('bye', $sourceClass->hello(-100));
    }

    public function test_add()
    {
        $sourceClass = new AnotherSourceClass();
        $this->assertSame(4, $sourceClass->add(2, 2));
    }

}
