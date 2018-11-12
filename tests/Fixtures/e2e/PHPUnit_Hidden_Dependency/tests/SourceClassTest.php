<?php

namespace PHPUnit_Hidden_Dependency\Test;

use PHPUnit_Hidden_Dependency\SourceClass;
use PHPUnit\Framework\TestCase;

class SourceClassTest extends TestCase
{
    private static $counter = 0;

    public function test_hello()
    {
        $sourceClass = new SourceClass();
        $this->assertSame('hello', $sourceClass->hello());
    }

    public function test_first(): void
    {
        $this->assertSame(0, self::$counter++);
    }

    public function test_second(): void
    {
        $this->assertSame(1, self::$counter);
    }
}
