<?php

namespace Ignore_All_Mutations\Test;

use Ignore_All_Mutations\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello(100));
        $this->assertSame('bye', $sourceClass->hello(-100));
    }

    public function test_add()
    {
        $sourceClass = new SourceClass();
        $this->assertSame(4, $sourceClass->add(2, 2));
    }

}
